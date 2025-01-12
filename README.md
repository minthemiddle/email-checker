# Email Verification and Analysis Tool

A comprehensive email verification and analysis tool that provides:

- Email format validation
- Domain existence checking
- DNS record analysis (MX, SPF, DMARC, DKIM)
- Email classification (personal vs mass/distribution)
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

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Set up environment variables:
   - Create a `.env` file
   - Add your OpenAI API key:
     ```env
     OPENAI_API_KEY=your_api_key_here
     ```

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
