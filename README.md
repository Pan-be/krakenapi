# Kraken API Candle Fetcher & Indicator Processor

## Overview

This PHP-based project fetches candlestick data from **Kraken Futures API**, processes it, and enhances it with technical indicators. It stores results in JSON and CSV formats.

## Features

* Fetch candlestick data from Kraken Futures API
* Transform raw API data into standardized OHLCV format
* Apply technical indicators (SMA, EMA, Ichimoku, ADX, SuperTrend)
* Store processed candles in JSON and CSV
* CLI tool for JSON → CSV conversion
* REST endpoint for retrieving processed candles

## Installation

1. Clone the repo:

```bash
git clone https://github.com/Pan-be/krakenapi.git
cd krakenapi
```

2. Ensure PHP 8.0+ with cURL is installed
3. Set write permissions for `storage/`

```bash
chmod -R 777 storage/
```

4. Adjust `config/pairs.json` and `config/allowedIntervals.json` if needed

## Usage

### Web API

Start PHP built-in server:

```bash
php -S localhost:8080
```

Fetch candles:

```bash
curl "http://localhost:8080/index.php?interval=1h"
```

### CLI

Convert JSON candles to CSV:

```bash
php scripts/jsonToCsv.php 1h
php scripts/jsonToCsv.php 1h --pair=Pf_XBTUSD
```

## Configuration

* `config/pairs.json` → list of trading pairs
* `config/allowedIntervals.json` → allowed intervals (1m,5m,15m,1h,4h,1d,1w)

## Contributing

1. Fork repository
2. Create a new branch
3. Submit a Pull Request

## License

MIT License ([see LICENSE](../LICENSE))