# Kraken API Candle Fetcher & Indicator Processor - Documentation

## Project Overview

Fetches and processes candlestick data from **Kraken Futures API** and enriches it with multiple technical indicators. Stores results in JSON and CSV files per trading pair and interval.

## Project Structure

```
config/            # JSON configs for pairs and allowed intervals
scripts/           # CLI tools (jsonToCsv.php)
src/               # Source code
  Controllers/     # CandleFetcherController.php
  Core/            # Config, Request, ErrorHandler
  Indicators/      # Technical indicators and calculator
  Services/        # CandleProcessor.php, KrakenService.php
storage/           # Output JSON/CSV files
autoload.php       # PSR-4 like autoloader
index.php          # Entry point
docs/              # Full technical documentation
```

## Installation & Setup

* PHP 8.0+, cURL
* Composer optional
* Writable `storage/`
* Internet access to Kraken Futures API

## Usage

### Web API Endpoint

```bash
php -S localhost:8080
curl "http://localhost:8080/index.php?interval=1h"
```

### CLI Tool

```bash
php scripts/jsonToCsv.php 1h
php scripts/jsonToCsv.php 1h --pair=Pf_XBTUSD
```

Output: `storage/csv/<interval>/<pair>.csv`

## Configuration

* `pairs.json` → list of trading pairs
* `allowedIntervals.json` → allowed intervals

## Technical Indicators

Applied via `IndicatorCalculator::applyAll()`:

* SMA20, SMA50, SMA200
* EMA20, EMA50, EMA200
* Ichimoku: Tenkan, Kijun, SenkouA, SenkouB
* ADX, SuperTrend
* Planned: RSI, MACD, OBV, Bollinger Bands, Ichimoku Chikou

## Error Handling

* Invalid intervals → HTTP 400
* API errors → returned in JSON
* Exceptions → handled by `ErrorHandler.php`

## Data Storage

* JSON: `storage/candles/<interval>/<pair>.json`
* CSV: `storage/csv/<interval>/<pair>.csv`

## Extending the Project

* Add new indicators in `src/Indicators/` and include in `IndicatorCalculator::applyAll()`
* Add new pairs to `pairs.json`
* Add new intervals to `allowedIntervals.json`

## Contributing

1. Fork
2. Branch
3. Pull Request

## License

MIT License ([see LICENSE](../LICENSE))
