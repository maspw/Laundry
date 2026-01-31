CREATE TABLE IF NOT EXISTS stock_analysis_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticker VARCHAR(10) NOT NULL,
    avg_price DECIMAL(15, 2),
    current_price DECIMAL(15, 2),
    action_advice VARCHAR(50),
    detail_advice TEXT,
    chart_path VARCHAR(255),
    analyzed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
