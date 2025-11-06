import argparse, csv, os, re, sys, time
from bs4 import BeautifulSoup

from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

HDRS_JS = [
    "--headless=new", "--no-sandbox", "--disable-gpu",
    "--window-size=1440,3000", "--disable-dev-shm-usage"
]

TEAM_CODES = {
    "SSG": "SSG Landers",
    "LG":  "LG Twins",
    "OB":  "Doosan Bears", "DS": "Doosan Bears",
    "NC":  "NC Dinos",
    "KT":  "KT Wiz",
    "KIA": "KIA Tigers",
    "LT":  "Lotte Giants",
    "HH":  "Hanwha Eagles",
    "WO":  "Kiwoom Heroes",
    "SS":  "Samsung Lions",
}

def t(x): return (x or "").strip()

def ensure_header(path):
    header = ["player_ext_id","pitcher_name","league","season","game_date",
              "home_team","away_team","team_side","opponent_team","is_starting",
              "ip_str","r","er","h","bb","k","hr","bf","pitches","strikes",
              "decision","source_url"]
    need = (not os.path.exists(path)) or os.path.getsize(path)==0
    if need:
        os.makedirs(os.path.dirname(path), exist_ok=True)
        with open(path,"w",newline="",encoding="utf-8") as f:
            csv.writer(f).writerow(header)

def to_int(s):
    s = t(s).replace(",","")
    return int(s) if s.isdigit() else 0

def normalize_decision(s):
    u = t(s).upper()
    if "승" in s or u=="W": return "W"
    if "패" in s or u=="L": return "L"
    if "세" in s or "SV" in u or u=="S": return "S"
    if "홀" in s or "HLD" in u or u=="H": return "H"
    if "BS" in u or "블론" in s: return "BS"
    return "ND"

def parse_from_gameid(url):
    m = re.search(r"gameId=(\d{8})([A-Z0-9]+)", url)
    if not m:
        return None, None, None
    date8, tail = m.groups()
    y,mm,dd = date8[:4], date8[4:6], date8[6:8]
    date_txt = f"{y}-{mm}-{dd}"
    tail = re.sub(r"\d+$", "", tail)
    codes = sorted(TEAM_CODES.keys(), key=lambda x: -len(x))
    away_code = home_code = None
    for c in codes:
        if tail.startswith(c):
            away_code = c
            tail = tail[len(c):]
            break
    if away_code:
        for c in codes:
            if tail.startswith(c):
                home_code = c
                break
    away = TEAM_CODES.get(away_code)
    home = TEAM_CODES.get(home_code)
    return date_txt, home, away

def pick_pitcher_tables_from_html(html):
    soup = BeautifulSoup(html, "html.parser")
    date_txt = None
    for sel in ["h2","h3","h4",".game",".tit",".title",".date","strong"]:
        for el in soup.select(sel):
            m = re.search(r"(\d{4})[.-](\d{1,2})[.-](\d{1,2})", t(el.get_text()))
            if m:
                y,mm,dd = m.groups()
                date_txt = f"{y}-{int(mm):02d}-{int(dd):02d}"
                break
        if date_txt: break

    home = away = None
    for s in [t(c.get_text()) for c in soup.find_all("caption")] + \
               [t(h.get_text()) for h in soup.find_all(["h2","h3","h4","strong"])]:
        if "투수" in s or "Pitchers" in s:
            name = s.replace("투수","").replace("Pitchers","").strip()
            if name and not home:
                home = name
            elif name and home and not away and name != home:
                away = name

    tables = []
    for tbl in soup.select("table"):
        title = None
        h = tbl.find_previous(["h2","h3","h4","strong"])
        if h: title = t(h.get_text())
        cap = tbl.find("caption")
        if not title and cap: title = t(cap.get_text())
        if not title: continue
        if ("투수" in title) or ("Pitchers" in title):
            tables.append((title, tbl))

    if not tables:
        for tbl in soup.select("table"):
            head = tbl.select_one("thead tr") or tbl.find("tr")
            if not head: continue
            ths = [t(x.get_text()) for x in head.find_all(["th","td"])]
            if not ths: continue
            keys = ["이닝","IP","탈삼진","SO","피안타","H","볼넷","BB","투구수","NP"]
            hit = sum(any(k in h for h in ths) for k in keys)
            if hit >= 3:
                tables.append(("PITCHERS_BY_HEADER", tbl))

    return soup, date_txt, home, away, tables

def extract_rows(title, tbl, date_txt, home, away, season="2024", league="KBO", url=""):
    head = tbl.select_one("thead tr") or tbl.find("tr")
    ths = [t(x.get_text()) for x in head.find_all(["th","td"])] if head else []

    if home and home.lower() in title.lower():
        team_side = "home"; team_name = home; opp_name = away
    elif away and away.lower() in title.lower():
        team_side = "away"; team_name = away; opp_name = home
    else:
        team_side = None; team_name = None; opp_name = None

    def get_cell(tds, key_ko, key_en):
        idx = -1
        for i,h in enumerate(ths):
            if h==key_ko or h==key_en: idx=i; break
        if idx==-1:
            for i,h in enumerate(ths):
                if key_ko in h or key_en in h: idx=i; break
        return t(tds[idx].get_text()) if idx!=-1 and idx<len(tds) else ""

    rows = []
    first = True
    body = tbl.select("tbody tr") or tbl.find_all("tr")[1:]
    for tr in body:
        tds = tr.find_all("td")
        if not tds: 
            continue
        name_cell = tds[0]
        pitcher_name = t(name_cell.get_text())
        if not pitcher_name or pitcher_name in ("합계","Totals"):
            continue
        player_ext_id = ""
        a = name_cell.find("a")
        if a and a.get("href",""):
            m = re.search(r"playerId=(\d+)", a.get("href"))
            if m: player_ext_id = m.group(1)

        ip = get_cell(tds, "이닝","IP").replace("⅓",".1").replace("⅔",".2")
        r_ = get_cell(tds, "실점","R")
        er = get_cell(tds, "자책","ER")
        h_ = get_cell(tds, "피안타","H")
        bb = get_cell(tds, "볼넷","BB")
        so = get_cell(tds, "탈삼진","SO")
        hr = get_cell(tds, "피홈런","HR")
        bf = get_cell(tds, "타자","BF")
        np = get_cell(tds, "투구수","NP")
        st = get_cell(tds, "스트라이크","ST")
        note = get_cell(tds, "비고","Note")

        rows.append({
            "player_ext_id": player_ext_id,
            "pitcher_name": pitcher_name,
            "league": league,
            "season": season,
            "game_date": date_txt,
            "home_team": team_name or "",
            "away_team": opp_name or "",
            "team_side": team_side if team_side else "",
            "opponent_team": opp_name or "",
            "is_starting": "true" if first else "false",
            "ip_str": ip,
            "r": to_int(r_), "er": to_int(er), "h": to_int(h_),
            "bb": to_int(bb), "k": to_int(so), "hr": to_int(hr),
            "bf": to_int(bf), "pitches": to_int(np), "strikes": to_int(st),
            "decision": normalize_decision(note),
            "source_url": url
        })
        first = False

    return rows, team_side

def click_pitcher_tab(driver):
    candidates = driver.find_elements(By.XPATH, "//a[contains(.,'투수')]|//button[contains(.,'투수')]|//a[contains(.,'Pitcher')]|//button[contains(.,'Pitcher')]")
    for el in candidates:
        try:
            driver.execute_script("arguments[0].scrollIntoView({block:'center'});", el)
            el.click()
            time.sleep(0.6)
            return True
        except Exception:
            continue
    return False

def collect_from_current_context(driver, url, season, league):
    html = driver.page_source
    soup, date_txt, home, away, tables = pick_pitcher_tables_from_html(html)
    if not date_txt or not (home and away):
        d2, h2, a2 = parse_from_gameid(url)
        if not date_txt and d2: date_txt = d2
        if not home and h2:     home = h2
        if not away and a2:     away = a2

    rows_all = []
    team_sides = []
    for idx,(title,tbl) in enumerate(tables):
        rows, side = extract_rows(title, tbl, date_txt, home, away, season, league, url)
        if rows and not rows[0]["team_side"]:
            side = "home" if idx==0 else "away"
            for r in rows:
                r["team_side"] = side
                if side == "home":
                    r["home_team"] = home or r["home_team"]
                    r["away_team"] = away or r["away_team"]
                    r["opponent_team"] = away or r["opponent_team"]
                else:
                    r["home_team"] = home or r["home_team"]
                    r["away_team"] = away or r["away_team"]
                    r["opponent_team"] = home or r["opponent_team"]
        rows_all.extend(rows)
        team_sides.append(side)

    return rows_all, date_txt, home, away, soup

def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--url", required=True)
    ap.add_argument("--out", default="data/parsed_csv/pitcher_logs_2024.csv")
    ap.add_argument("--season", default="2024")
    ap.add_argument("--league", default="KBO")
    args = ap.parse_args()

    ensure_header(args.out)

    opts = Options()
    for a in HDRS_JS: opts.add_argument(a)
    driver = webdriver.Chrome(options=opts)

    try:
        driver.get(args.url)

        WebDriverWait(driver, 6).until(EC.presence_of_all_elements_located((By.TAG_NAME, "body")))
        time.sleep(1.0)
        click_pitcher_tab(driver)
        rows, date_txt, home, away, soup = collect_from_current_context(driver, args.url, args.season, args.league)

        if not rows:
            iframes = driver.find_elements(By.TAG_NAME, "iframe")
            for i, frm in enumerate(iframes):
                try:
                    driver.switch_to.frame(frm)
                    time.sleep(0.6)
                    click_pitcher_tab(driver)
                    r2, date2, h2, a2, _ = collect_from_current_context(driver, args.url, args.season, args.league)
                    rows.extend(r2)
                    if (not date_txt) and date2: date_txt = date2
                    if (not home) and h2: home = h2
                    if (not away) and a2: away = a2
                    driver.switch_to.default_content()
                except Exception:
                    driver.switch_to.default_content()
                    continue

        if not rows:
            os.makedirs(os.path.dirname(args.out), exist_ok=True)
            with open("data/parsed_csv/_debug_boxscore.html","w",encoding="utf-8") as f:
                f.write(driver.page_source)
            driver.save_screenshot("data/parsed_csv/_debug_boxscore.png")
            print("⚠️ 투수 테이블을 못 찾음. _debug_boxscore.* 파일 확인.")
            sys.exit(1)

        with open(args.out,"a",newline="",encoding="utf-8") as f:
            w = csv.writer(f)
            for r in rows:
                w.writerow([r[c] for c in ["player_ext_id","pitcher_name","league","season","game_date",
                                           "home_team","away_team","team_side","opponent_team","is_starting",
                                           "ip_str","r","er","h","bb","k","hr","bf","pitches","strikes",
                                           "decision","source_url"]])

        print(f"✅ {len(rows)} rows → {args.out} | {date_txt or ''} {(home or '')} vs {(away or '')}")

    finally:
        driver.quit()

if __name__ == "__main__":
    main()
