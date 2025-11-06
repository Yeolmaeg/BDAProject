import requests
from bs4 import BeautifulSoup
from urllib.parse import urlparse, parse_qs
import pandas as pd
from typing import List, Dict, Any, Optional
import time
import random
import os


BASE_URL = "https://www.statiz.co.kr/player/?m=day"
DEFAULT_HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/118.0.0.0 Safari/537.36"
    ),
    "Referer": "https://www.statiz.co.kr/",
    "Accept-Language": "ko-KR,ko;q=0.9,en-US;q=0.8,en;q=0.7",
}


def fetch_statiz_pitcher_html(
    p_no: str,
    year: int,
    session: Optional[requests.Session] = None,
    max_retries: int = 3,
    sleep_range=(1, 3),
) -> str:
 
    url = f"{BASE_URL}&p_no={p_no}&pos=pitching&year={year}"

    sess = session or requests.Session()

    last_exc = None
    for _ in range(max_retries):
        try:
            resp = sess.get(url, headers=DEFAULT_HEADERS, timeout=10)
            resp.raise_for_status()
            return resp.text
        except requests.HTTPError as e:
            last_exc = e
            time.sleep(random.uniform(*sleep_range))
        except requests.RequestException as e:
            last_exc = e
            time.sleep(random.uniform(*sleep_range))

    raise RuntimeError(f"Failed to fetch statiz page for p_no={p_no}, year={year}: {last_exc}")


def parse_pitcher_daily_html(
    html: str,
    fallback_p_no: str = "",
    fallback_year: str = "",
) -> pd.DataFrame:

    soup = BeautifulSoup(html, "html.parser")

    # 페이지 안에서 p_no 찾을 수 있으면 사용
    p_no_input = soup.find("input", {"name": "p_no"})
    if p_no_input and p_no_input.get("value"):
        player_id = p_no_input.get("value").strip()
    else:
        player_id = fallback_p_no

    year_input = soup.find("input", {"name": "year"})
    if year_input and year_input.get("value"):
        year = year_input.get("value").strip()
    else:
        year = str(fallback_year) if fallback_year else ""

    name_box = soup.select_one("div.t_name")
    if name_box:
        player_name = name_box.get_text(" ", strip=True).split(" ")[0]
    else:
        player_name = ""

    rows_out: List[Dict[str, Any]] = []

    tbodies = soup.find_all("tbody")
    for tbody in tbodies:
        trs = tbody.find_all("tr")
        for tr in trs:
            tds = tr.find_all("td")
            if not tds:
                continue

            raw_date = tds[0].get_text(strip=True)
            if not raw_date:
                continue

            if year:
                game_date = f"{year}-{raw_date}"
            else:
                game_date = raw_date 
            game_id = ""
            if len(tds) > 2:
                a_tag = tds[2].find("a")
                if a_tag and "s_no=" in a_tag.get("href", ""):
                    qs = parse_qs(urlparse(a_tag["href"]).query)
                    game_id = qs.get("s_no", [""])[0]
            ip = tds[4].get_text(strip=True) if len(tds) > 4 else ""
            so = tds[17].get_text(strip=True) if len(tds) > 17 else ""
            np_ = tds[18].get_text(strip=True) if len(tds) > 18 else ""
            era = tds[23].get_text(strip=True) if len(tds) > 23 else ""
            dec = tds[28].get_text(strip=True) if len(tds) > 28 else ""

            rows_out.append(
                {
                    "game_date": game_date,
                    "game_id": game_id,
                    "player_id": player_id,
                    "player_name": player_name,
                    "IP": ip,
                    "SO": so,
                    "NP": np_,
                    "ERA": era,
                    "DEC": dec,
                }
            )

    return pd.DataFrame(rows_out)


def crawl_pitchers(
    players: List[str],
    years: List[int],
    output_dir: str = ".",
) -> None:
  
    os.makedirs(output_dir, exist_ok=True)
    session = requests.Session()

    for p_no in players:
        all_df_list = []
        for year in years:
            try:
                html = fetch_statiz_pitcher_html(p_no=p_no, year=year, session=session)
            except RuntimeError as e:
                print(e)
                continue

            df = parse_pitcher_daily_html(html, fallback_p_no=p_no, fallback_year=str(year))
            df["year"] = year
            all_df_list.append(df)
            time.sleep(random.uniform(0.5, 1.5))

        if not all_df_list:
            continue

        final_df = pd.concat(all_df_list, ignore_index=True)

        # 파일명: pitcher_<p_no>.csv
        out_path = os.path.join(output_dir, f"pitcher_{p_no}.csv")
        final_df.to_csv(out_path, index=False, encoding="utf-8-sig")
        print(f"[DONE] p_no={p_no} → {out_path}, rows={len(final_df)}")


def main():
      
    players = ["10058"]  # statiz 선수 id 
    years = [2024]

    crawl_pitchers(players, years, output_dir="./out_statiz")


if __name__ == "__main__":
    main()
