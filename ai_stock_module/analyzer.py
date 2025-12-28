import easyocr
import yfinance as yf
import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import matplotlib.dates as mdates
import cv2
import json
import sys
import os
import re
import mysql.connector
from datetime import datetime

# Configure Matplotlib
plt.style.use('ggplot')

def get_db_connection():
    # Load config from JSON file
    config_path = os.path.join(os.path.dirname(__file__), 'db_config.json')
    try:
        with open(config_path, 'r') as f:
            db_config = json.load(f)

        conn = mysql.connector.connect(**db_config)
        return conn
    except mysql.connector.Error as err:
        # Silently fail or log to stderr
        sys.stderr.write(f"DB Connection Error: {err}\n")
        return None

def save_analysis_to_db(data):
    conn = get_db_connection()
    if not conn:
        return False

    try:
        cursor = conn.cursor()
        sql = """
            INSERT INTO stock_analysis_history
            (ticker, avg_price, current_price, action_advice, detail_advice, chart_path)
            VALUES (%s, %s, %s, %s, %s, %s)
        """
        val = (
            data['ticker'],
            data['user_avg_price'],
            data['market_price'],
            data['advice']['action'],
            data['advice']['details'],
            data['chart_image']
        )
        cursor.execute(sql, val)
        conn.commit()
        cursor.close()
        conn.close()
        return True
    except mysql.connector.Error as err:
        sys.stderr.write(f"DB Insert Error: {err}\n")
        return False

def parse_price(text):
    """
    Parses a price string like "4,750", "4.750", "4750" into a float.
    Assumes Indonesian context where thousands might be separated by dots or commas.
    """
    # Remove "Avg", "Rp", spaces
    clean = re.sub(r'(?i)(avg|avrg|rata|rp|[:])', '', text).strip()

    # Check format
    # Case 1: "4,750" -> 4750.0 (Standard US/International)
    # Case 2: "4.750" -> 4750.0 (Indonesian/European Thousand Separator)
    # Case 3: "4750" -> 4750.0

    # Heuristic: If there is a comma or dot, check position.
    # If it has 3 digits after the last separator, it's likely a thousand separator.

    try:
        if ',' in clean and '.' in clean:
             # e.g. "4.500,00" -> Remove dot, replace comma with dot
             clean = clean.replace('.', '').replace(',', '.')
        elif ',' in clean:
            # "4,500" -> "4500"
            clean = clean.replace(',', '')
        elif '.' in clean:
            # "4.500" -> "4500"
            # Be careful of small decimals "4.5"
            parts = clean.split('.')
            if len(parts[-1]) == 3: # Likely thousand separator
                 clean = clean.replace('.', '')
            else:
                 pass # likely decimal

        return float(clean)
    except ValueError:
        return None

def extract_data_from_image(image_path):
    """
    Uses EasyOCR to extract ticker and average price.
    Improved Logic: Uses Bounding Box (BBox) coordinates to pair Ticker and Price.
    """
    # Initialize EasyOCR
    # Suppress verbose output
    old_stdout = sys.stdout
    sys.stdout = open(os.devnull, 'w')
    reader = easyocr.Reader(['en', 'id'], gpu=False, verbose=False)
    result = reader.readtext(image_path)
    sys.stdout = old_stdout

    items = []
    for (bbox, text, conf) in result:
        (tl, tr, br, bl) = bbox
        center_x = (tl[0] + br[0]) / 2
        center_y = (tl[1] + br[1]) / 2

        items.append({
            'text': text.strip(),
            'clean_text': text.strip().upper(),
            'bbox': bbox,
            'center_x': center_x,
            'center_y': center_y,
            'matched': False # To track usage
        })

    extracted_data = []

    # 1. Identify Tickers
    tickers = []
    for item in items:
        # Match exactly 4 letters, uppercase
        if re.match(r'^[A-Z]{4}$', item['clean_text']) and item['clean_text'] not in ["JUAL", "BELI", "RUGI", "LABA", "DANA", "CASH", "IHSG", "AVRG"]:
            tickers.append(item)
            item['matched'] = True # Ticker itself is used

    # 2. Find associated Average Price for each ticker
    for ticker_item in tickers:
        ticker_name = ticker_item['clean_text']
        best_price = 0.0
        min_dist = float('inf')
        best_match_item = None

        for item in items:
            if item['matched']:
                continue # Skip already used items (prevents reusing price)

            # Check if text looks like a price (digits)
            # Must have digits. May have "Avg" prefix.
            if not any(char.isdigit() for char in item['text']):
                continue

            # Basic spatial check:
            # Item should be below ticker (Y > Ticker Y)
            if item['center_y'] > ticker_item['center_y']:

                # Horizontal overlap check (roughly same column)
                h_dist = abs(item['center_x'] - ticker_item['center_x'])
                v_dist = item['center_y'] - ticker_item['center_y']

                # Constraints
                # v_dist: shouldn't be too far (e.g. next card)
                # h_dist: strict alignment
                if h_dist < 150 and v_dist < 200:
                    # If it explicitly says "Avg", it's very likely the one
                    is_avg_label = "AVG" in item['clean_text'] or "RATA" in item['clean_text']

                    # Score: Distance, heavily favoring "Avg" label
                    score = v_dist - (1000 if is_avg_label else 0)

                    if score < min_dist:
                        # Try parsing
                        parsed_val = parse_price(item['text'])
                        if parsed_val is not None:
                            best_price = parsed_val
                            min_dist = score
                            best_match_item = item

        # Mark as matched so next ticker doesn't grab it
        if best_match_item:
            best_match_item['matched'] = True

        extracted_data.append({
            'ticker': f"{ticker_name}.JK",
            'avg_price': best_price
        })

    return extracted_data

def fetch_market_data(ticker):
    """
    Fetches 6 months of data from yfinance and calculates technical indicators.
    """
    try:
        import logging
        logging.getLogger('yfinance').setLevel(logging.CRITICAL)

        stock = yf.Ticker(ticker)
        df = stock.history(period="1y")

        if df.empty:
            return None

        # Calculate Indicators
        df['SMA_20'] = df['Close'].rolling(window=20).mean()
        df['SMA_50'] = df['Close'].rolling(window=50).mean()

        # RSI 14
        delta = df['Close'].diff()
        gain = (delta.where(delta > 0, 0)).rolling(window=14).mean()
        loss = (-delta.where(delta < 0, 0)).rolling(window=14).mean()
        rs = gain / loss
        df['RSI'] = 100 - (100 / (1 + rs))

        # MACD (12, 26, 9)
        exp12 = df['Close'].ewm(span=12, adjust=False).mean()
        exp26 = df['Close'].ewm(span=26, adjust=False).mean()
        df['MACD'] = exp12 - exp26
        df['Signal_Line'] = df['MACD'].ewm(span=9, adjust=False).mean()

        # Support & Resistance (Min/Max over last 60 days)
        last_60 = df.tail(60)
        support = last_60['Low'].min()
        resistance = last_60['High'].max()

        return {
            'data': df,
            'current_price': df['Close'].iloc[-1],
            'support': support,
            'resistance': resistance,
            'rsi': df['RSI'].iloc[-1],
            'sma_20': df['SMA_20'].iloc[-1],
            'sma_50': df['SMA_50'].iloc[-1],
            'macd': df['MACD'].iloc[-1],
            'signal': df['Signal_Line'].iloc[-1]
        }
    except Exception:
        return None

def generate_advice(market_data, user_data):
    ticker = user_data['ticker']
    avg_price = user_data['avg_price']
    current_price = market_data['current_price']
    rsi = market_data['rsi']
    sma_20 = market_data['sma_20']
    support = market_data['support']

    # Floating calculation
    if avg_price > 0:
        floating_pct = ((current_price - avg_price) / avg_price) * 100
    else:
        floating_pct = 0.0

    advice = []
    action = "HOLD"

    # Logic Rules
    # 1. Take Profit
    if rsi > 70 and current_price > sma_20:
        action = "TAKE PROFIT"
        advice.append(f"RSI udah {rsi:.2f} (Overbought) dan harga di atas SMA-20. Waktunya amanin cuan, Bestie!")

    # 2. Speculative Buy
    elif current_price <= (support * 1.02) and rsi < 30: # Within 2% of support
        action = "SPECULATIVE BUY"
        advice.append(f"Harga lagi diskon deket Support ({support:,.0f}) + RSI Oversold ({rsi:.2f}). Gas tipis-tipis!")

    # 3. Average Down / Stop Loss
    if floating_pct < -5:
        advice.append(f"Lu lagi floating loss {floating_pct:.2f}%.")
        if current_price < support:
             advice.append("Warning: Harga udah jebol support. Pertimbangkan Cut Loss biar nggak makin boncos.")
        else:
             advice.append(f"Kalau masih yakin fundamental oke, bisa Average Down di area {support:,.0f}.")

    # Default logic
    if action == "HOLD":
        if floating_pct > 0:
            advice.append("Hold dulu, tren masih oke atau belum ada sinyal jual kuat. Let your profit run!")
        elif floating_pct == 0:
            advice.append("Avg Price tidak terdeteksi atau 0. Cek grafik teknikal aja ya.")
        else:
            advice.append("Wait and see. Jangan fomo, pasar lagi galau.")

    return {
        'ticker': ticker,
        'action': action,
        'details': " ".join(advice),
        'stats': {
            'rsi': round(rsi, 2),
            'support': support,
            'resistance': market_data['resistance'],
            'floating_pct': round(floating_pct, 2)
        }
    }

def visualize_all(market_data, user_data, output_dir="ai_stock_module/output"):
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)

    df = market_data['data'].tail(120)
    ticker = user_data['ticker']
    avg_price = user_data['avg_price']

    plt.figure(figsize=(10, 5))

    # Plot Price
    plt.plot(df.index, df['Close'], label='Price', color='black', alpha=0.7)

    # Plot SMAs
    plt.plot(df.index, df['SMA_20'], label='SMA-20', color='orange', linestyle='--')
    plt.plot(df.index, df['SMA_50'], label='SMA-50', color='blue', linestyle='--')

    # Plot User Avg Price
    if avg_price > 0:
        plt.axhline(y=avg_price, color='purple', linestyle='-', linewidth=2, label=f'Avg ({avg_price:,.0f})')

    # Plot Support/Resistance
    plt.axhline(y=market_data['support'], color='green', linestyle=':', label='Support')

    plt.title(f"Analisis {ticker}")
    plt.legend()
    plt.grid(True)

    # Format Date
    plt.gca().xaxis.set_major_formatter(mdates.DateFormatter('%m-%d'))

    # Save
    timestamp = datetime.now().strftime("%Y%m%d%H%M%S")
    filename = f"chart_{ticker.replace('.', '')}_{timestamp}.png"
    filepath = os.path.join(output_dir, filename)
    plt.savefig(filepath)
    plt.close()

    return filepath

def main():
    if len(sys.argv) < 2:
        image_path = "ai_stock_module/mock_portfolio.png"
    else:
        image_path = sys.argv[1]

    if not os.path.exists(image_path):
        print(json.dumps({"error": "Image file not found"}))
        return

    # Extract
    extracted_items = extract_data_from_image(image_path)

    if not extracted_items:
        print(json.dumps({"error": "No tickers found"}))
        return

    results = []

    for item in extracted_items:
        market_data = fetch_market_data(item['ticker'])

        if not market_data:
             results.append({'ticker': item['ticker'], 'error': "Data Error"})
             continue

        advice = generate_advice(market_data, item)
        chart_path = visualize_all(market_data, item)

        result_item = {
            'ticker': item['ticker'],
            'user_avg_price': item['avg_price'],
            'market_price': market_data['current_price'],
            'advice': advice,
            'chart_image': chart_path
        }

        # Save to DB
        save_analysis_to_db(result_item)

        results.append(result_item)

    print(json.dumps(results, indent=2))

if __name__ == "__main__":
    main()
