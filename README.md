# Email Verification and Analysis Tool

A comprehensive email verification and analysis tool that provides:

- Email format validation
- Domain existence checking
- DNS record analysis (MX, SPF, DMARC, DKIM)
- Email classification (personal vs mass/distribution)
- Domain classification and industry analysis
- Website screenshot capture
- Web interface and CLI support

## Features

### Email Verification
- Validates email format using MailChecker library
- Checks if domain exists and is accessible
- Detects disposable email addresses

### DNS Analysis
- MX Records: Checks mail server configuration
- SPF: Verifies Sender Policy Framework record
- DMARC: Checks Domain-based Message Authentication
- DKIM: Validates DomainKeys Identified Mail configuration

### Email Classification
- Uses OpenAI GPT-4 to classify email addresses as:
  - Personal/private email addresses
  - Mass/distribution email addresses
- Provides reasoning for classification

### Domain Analysis
- Website content analysis using OpenAI GPT-4
- Industry classification including:
  - WZ-Code (German industry classification)
  - Industry sector
  - Organization type (Company, Association, Politics, Other)
  - One-line description
- Country detection based on website content
- Website screenshot capture using ScreenshotMachine API

### Web Interface Features
- Interactive results display with:
  - Visual domain status indicator (traffic light system)
  - Clickable domain links
  - Expandable DNS record details
  - Website screenshot display
  - Detailed classification results
- Responsive design using Tailwind CSS
- Interactive components using Alpine.js

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Set up environment variables:
   - Copy `.env.sample` to `.env`:
     ```bash
     cp .env.sample .env
     ```
   - Edit the `.env` file and add your API keys:
     ```env
     OPENAI_API_KEY=your_openai_api_key_here
     SCREENSHOTMACHINE_API_KEY=your_screenshotmachine_api_key_here
     ```
   - Never commit your `.env` file to version control!

## Usage

### Web Interface
Start the built-in PHP server:
```bash
php index.php --serve
```
Access the web interface at: http://localhost:8080

### Command Line Interface
Basic usage:
```bash
php index.php --email=test@example.com
```

Optional flags:
- `--verify-existence`: Attempt to verify email existence via SMTP
- `--serve`: Start web server

## Requirements
- PHP 8.0 or higher
- Composer
- OpenAI API key

## License
MIT License

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## Acknowledgements
- MailChecker library for email validation
- OpenAI for email classification
- Alpine.js for web interface interactivity
- Tailwind CSS for styling

## Screenshot

![Screenshot](screenshot.png)
