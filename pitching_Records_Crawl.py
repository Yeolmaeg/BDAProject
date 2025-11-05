"""
 대상 페이지 예시:
  https://www.koreabaseball.com/Record/Player/PitcherDetail/Daily.aspx?playerId=67609
"""

import re
import requests
import pandas as pd
from bs4 import BeautifulSoup
from datetime import datetime
from pathlib import Path

try:
    from google.colab import files
    ON_COLAB = True
except ImportError:
    ON_COLAB = False

# 아래에 모든 투ㅅ의 "일자별 페이지" 넣기
PITCHER_DAILY_URLS = [
    "https://www.koreabaseball.com/Record/Player/PitcherDetail/Daily.aspx?playerId=67609",
    # "https://www.koreabaseball.com/Record/Player/PitcherDetail/Daily.aspx?playerId=다른ID", ...
]

TARGET_YEAR = "2024"
OUT_FILE = "kbo_pitching_2024_manual.csv"

HEADERS = {
    "User-Agent": "Mozilla/5.0",
    "Accept-Language": "ko,en;q=0.9",
    "Referer": "https://www.koreabaseball.com/",
}

TARGET_COLUMNS = [
    "game_date",
    "home_team",
    "away_team",
    "pitcher_name",
    "innings_pitched",
    "era",
    "strikeouts",
    "pitch_count",
    "win_lost",
]

def fetch_html(url: str) -> str:
    resp = requests.get(url, headers=HEADERS, timeout=15)
    resp.raise_for_status()
    return resp.text

def extract_player_name(html: str) -> str:

    soup = BeautifulSoup(html, "lxml")
    txt = soup.get_text(" ", strip=True)
    m = re.search(r"([가-힣A-Za-z0-9\s\.\-]+)\s*투수일자별기록", txt)
    if m:
        return m.group(1).strip()
    return ""

def normalize_number(v, default="0"):
    if v is None:
        return default
    s = str(v).strip()
    s = re.sub(r"[^\d\.]", "", s)
    return s or default

def parse_daily_page(url: str, year: str = "2024") -> pd.DataFrame:
    html = fetch_html(url)
    player_name = extract_player_name(html)

    try:
        tables = pd.read_html(html)
    except ValueError:
        tables = []

    if not tables:
        return pd.DataFrame(columns=TARGET_COLUMNS)

    target = None
    for df in tables:
        cols_str = "".join(str(c) for c in df.columns)
        if "일자" in cols_str:
            target = df
            break

    if target is None:
        return pd.DataFrame(columns=TARGET_COLUMNS)

    target.columns = [str(c).strip() for c in target.columns]

    # 연도 필터링
    if "일자" in target.columns:
        target = target[target["일자"].astype(str).str.startswith(year)]

    rows = []
    for _, row in target.iterrows():
        raw_date = str(row.get("일자", "")).strip()
        game_date = raw_date
        parsed = False
        for fmt in ("%Y-%m-%d", "%Y.%m.%d"):
            try:
                game_date = datetime.strptime(raw_date[:10], fmt).date().isoformat()
                parsed = True
                break
            except Exception:
                continue
        if not parsed and len(raw_date) >= 10:
            game_date = raw_date[:10]

        ip = str(row.get("이닝", "")).strip()
        era = normalize_number(row.get("ERA", ""), default="")
        so = normalize_number(row.get("삼진", ""), default="0")
        pc = normalize_number(row.get("투구수", ""), default="0")
        wl = str(row.get("결과", "")).strip().upper()
        wl = wl if wl else "ND"

        rows.append({
            "game_date": game_date,
            "home_team": "",
            "away_team": "",
            "pitcher_name": player_name,
            "innings_pitched": ip,
            "era": era,
            "strikeouts": so,
            "pitch_count": pc,
            "win_lost": wl,
        })

    return pd.DataFrame(rows, columns=TARGET_COLUMNS)

def main():
    all_dfs = []

    for url in PITCHER_DAILY_URLS:
        print(f"[INFO] fetching {url}")
        df = parse_daily_page(url, year=TARGET_YEAR)
        print(f"  -> {len(df)} rows")
        if not df.empty:
            all_dfs.append(df)

    if all_dfs:
        final_df = pd.concat(all_dfs, ignore_index=True)
    else:
        final_df = pd.DataFrame(columns=TARGET_COLUMNS)

    final_df.to_csv(OUT_FILE, index=False, encoding="utf-8-sig")
    print(f"[DONE] wrote {OUT_FILE} ({len(final_df)} rows)")

    if ON_COLAB:
        from google.colab import files
        files.download(OUT_FILE)

if __name__ == "__main__":
    main()
