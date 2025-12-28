from PIL import Image, ImageDraw, ImageFont
import os

def create_mock_portfolio(filename="mock_portfolio.png"):
    # Create a blank white image
    width, height = 800, 600
    image = Image.new('RGB', (width, height), color='white')
    draw = ImageDraw.Draw(image)

    # Try to load a font, otherwise use default
    try:
        font_large = ImageFont.truetype("DejaVuSans-Bold.ttf", 40)
        font_medium = ImageFont.truetype("DejaVuSans.ttf", 24)
        font_small = ImageFont.truetype("DejaVuSans.ttf", 16)
    except IOError:
        font_large = ImageFont.load_default()
        font_medium = ImageFont.load_default()
        font_small = ImageFont.load_default()

    # Draw "Mock Portfolio" Header
    draw.text((20, 20), "Portfolio Overview", fill="black", font=font_large)

    # Draw Stock Item Card (Simulating Stockbit/Bibit layout)
    # Card Background
    draw.rectangle([(20, 100), (300, 250)], outline="gray", width=2)

    # Ticker
    draw.text((30, 110), "BBRI", fill="black", font=font_large)

    # Current Price
    draw.text((30, 160), "4,500", fill="green", font=font_medium)
    draw.text((120, 165), "(Current)", fill="gray", font=font_small)

    # Average Price - Place it clearly below Ticker
    draw.text((30, 200), "Avg: 4,750", fill="black", font=font_medium)

    # Another Stock
    draw.rectangle([(320, 100), (600, 250)], outline="gray", width=2)

    # Ticker
    draw.text((330, 110), "GOTO", fill="black", font=font_large)

    # Current Price
    draw.text((330, 160), "68", fill="red", font=font_medium)

    # Average Price
    draw.text((330, 200), "Avg: 80", fill="black", font=font_medium)

    # Save
    path = os.path.join("ai_stock_module", filename)
    image.save(path)
    print(f"Mock image saved to {path}")

if __name__ == "__main__":
    create_mock_portfolio()
