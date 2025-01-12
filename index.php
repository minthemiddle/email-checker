<?php
use Fgribreau\MailChecker;
require __DIR__ . '/vendor/autoload.php';

// Web server functionality
function serveWebInterface() {
    if (php_sapi_name() === 'cli-server') {
        if (preg_match('/\.(?:css|js|ico)$/', $_SERVER["REQUEST_URI"])) {
            return false;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
            header('Content-Type: application/json');
            ob_start();
            $results = performEmailCheck($_POST['email']);
            ob_end_clean();
            echo json_encode($results);
            exit;
        }
        
        // Serve the HTML interface
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Checker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ 
    email: '', 
    loading: false,
    results: null,
    error: null,
    async checkEmail() {
        this.loading = true;
        this.results = null;
        this.error = null;
        
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(this.email)
            });
            this.results = await response.json();
        } catch (e) {
            this.error = 'An error occurred while checking the email';
        }
        
        this.loading = false;
    }
}">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">Email Checker</h1>
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <form @submit.prevent="checkEmail" class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" 
                               x-model="email"
                               class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Enter email address"
                               required>
                    </div>
                    
                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            :disabled="loading">
                        <span x-show="!loading">Check Email</span>
                        <span x-show="loading">Checking...</span>
                    </button>
                </form>
                
                <!-- Error Message -->
                <div x-show="error" 
                     class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <p x-text="error"></p>
                </div>
                
                <!-- Results -->
                <div x-show="results" class="mt-6 space-y-4">
                    <div class="border-t pt-4">
                        <h2 class="text-xl font-semibold mb-4">Results</h2>
                        
                        <!-- Basic Info -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <p class="font-medium">Email: <span x-text="results.email" class="font-normal"></span></p>
                            <p class="font-medium">Domain Status: <span x-text="results.domain_status" class="font-normal"></span></p>
                            <p class="font-medium">Domain: 
                                <a :href="'https://' + results.email.split('@')[1]" 
                                   target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 font-normal">
                                    <span x-text="results.email.split('@')[1]"></span>
                                </a>
                            </p>
                        </div>
                        
                        <!-- DNS Records -->
                        <div class="space-y-4">
                            <!-- MX Records -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium mb-2">MX Records</h3>
                                <template x-if="results.dns_checks.mx.exists">
                                    <div class="space-y-1">
                                        <template x-for="record in results.dns_checks.mx.records">
                                            <p>
                                                <span x-text="'Priority: ' + record.pri"></span>
                                                <span x-text="', Host: ' + record.target"></span>
                                            </p>
                                        </template>
                                    </div>
                                </template>
                                <p x-show="!results.dns_checks.mx.exists">No MX records found</p>
                            </div>
                            
                            <!-- SPF Record -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium mb-2">SPF Record</h3>
                                <p x-text="results.dns_checks.spf || 'Not found'"></p>
                            </div>
                            
                            <!-- DMARC Record -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium mb-2">DMARC Record</h3>
                                <p x-text="results.dns_checks.dmarc || 'Not found'"></p>
                            </div>
                            
                            <!-- DKIM Record -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium mb-2">DKIM Record</h3>
                                <template x-if="results.dns_checks.dkim">
                                    <div>
                                        <p>Selector: <span x-text="results.dns_checks.dkim.selector"></span></p>
                                        <p class="break-all">Record: <span x-text="results.dns_checks.dkim.record"></span></p>
                                    </div>
                                </template>
                                <p x-show="!results.dns_checks.dkim">Not found</p>
                            </div>
                            
                            <!-- Classification -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium mb-2">Email Classification</h3>
                                <p>Type: <span x-text="results.classification.type"></span></p>
                                <p>Reasoning: <span x-text="results.classification.reasoning"></span></p>
                            </div>

                            <!-- Domain Classification -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium mb-2">Domain Classification</h3>
                                <p>WZ-Code: <span x-text="results.industry.wz_code"></span></p>
                                <p>Branche: <span x-text="results.industry.industry"></span></p>
                                <p>Organisationstyp: <span x-text="results.industry.org_type"></span></p>
                                <p>Beschreibung: <span x-text="results.industry.description"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
        exit;
    }
}

// Function to perform email checks and return results
function getHomepageContent($domain) {
    $url = "https://r.jina.ai/https://$domain";
    try {
        $content = file_get_contents($url);
        return $content ?: '';
    } catch (Exception $e) {
        return '';
    }
}

function performIndustryClassification($content) {
    $prompt = <<<EOD
Analysiere den folgenden Text einer Webseite und bestimme:
1. Den Organisationstyp (Firma, Verband, Politik, Anderes)
2. Den WZ-Code (Wirtschaftszweigklassifikation in Deutschland)
3. Die Branchenbezeichnung
4. Eine einzeilige Beschreibung der Organisation

Gib die Antwort im JSON Format mit den Feldern 'org_type', 'wz_code', 'industry' und 'description'.

Webseiteninhalt:
EOD;

    $yourApiKey = getenv('OPENAI_API_KEY');
    if (!$yourApiKey) {
        return [
            'wz_code' => 'unknown',
            'industry' => 'OpenAI API key not configured',
            'org_type' => 'Anderes',
            'description' => 'API key not configured'
        ];
    }

    try {
        $client = OpenAI::client($yourApiKey);

        $result = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt . $content],
            ],
            'response_format' => [
                'type' => 'json_object'
            ]
        ]);

        return json_decode($result->choices[0]->message->content, true);
    } catch (Exception $e) {
        return [
            'wz_code' => 'unknown',
            'industry' => 'Error during industry classification: ' . $e->getMessage(),
            'org_type' => 'Anderes',
            'description' => 'Error during classification'
        ];
    }
}

function performEmailCheck($email) {
    $results = [
        'email' => $email,
        'domain_status' => '',
        'dns_checks' => [
            'mx' => [],
            'spf' => null,
            'dmarc' => null,
            'dkim' => null
        ],
        'classification' => [
            'type' => '',
            'reasoning' => ''
        ],
        'industry' => [
            'wz_code' => '',
            'industry' => '',
            'org_type' => '',
            'description' => ''
        ]
    ];

    if (!MailChecker::isValid($email)) {
        $results['domain_status'] = 'Invalid email format or disposable email detected';
        return $results;
    }

    $domain = substr(strrchr($email, "@"), 1);
    $domain = cleanDomain($domain);

    if (!domainExists($domain)) {
        $results['domain_status'] = 'Domain does not exist or is not accessible';
        return $results;
    }

    $results['domain_status'] = 'Valid and Active';
    
    // Check MX Records
    $mxInfo = getMxRecords($domain);
    $results['dns_checks']['mx'] = $mxInfo;

    // Check TXT Records
    $txtRecords = getTxtRecords($domain);

    // Check SPF
    $results['dns_checks']['spf'] = getSpfRecord($txtRecords);

    // Check DMARC
    $results['dns_checks']['dmarc'] = getDmarcRecord($txtRecords);

    // Check DKIM
    $results['dns_checks']['dkim'] = getDkimRecord($domain);

    // Email Classification
    $emailPrefix = strtok($email, '@');
    $llmResponse = performLLMCheck($emailPrefix);
    $results['classification']['type'] = $llmResponse['classification'] === 'Person' ? 
        'Persönliche Email-Adresse' : 'Verteiler-Email-Adresse';
    $results['classification']['reasoning'] = $llmResponse['description'];

    // Domain Industry Classification
    $homepageContent = getHomepageContent($domain);
    if ($homepageContent) {
        $industryResponse = performIndustryClassification($homepageContent);
        $results['industry']['org_type'] = $industryResponse['org_type'];
        $results['industry']['wz_code'] = $industryResponse['wz_code'];
        $results['industry']['industry'] = $industryResponse['industry'];
        $results['industry']['description'] = $industryResponse['description'];
    }

    return $results;
}

function cleanDomain($domain) {
    // Remove protocol if present
    $domain = preg_replace('#^https?://#', '', $domain);
    // Remove path if present
    $domain = strtok($domain, '/');
    return $domain;
}

function domainExists($domain) {
    return checkdnsrr($domain, 'ANY');
}

function getMxRecords($domain) {
    $records = dns_get_record($domain, DNS_MX);
    return [
        'exists' => !empty($records),
        'records' => $records
    ];
}

function getTxtRecords($domain) {
    return dns_get_record($domain, DNS_TXT);
}

function getDmarcRecord($txtRecords) {
    foreach ($txtRecords as $record) {
        if (isset($record['txt']) && strpos($record['txt'], 'v=DMARC1') === 0) {
            return $record['txt'];
        }
    }
    return null;
}

function getSpfRecord($txtRecords) {
    foreach ($txtRecords as $record) {
        if (isset($record['txt']) && strpos($record['txt'], 'v=spf1') === 0) {
            return $record['txt'];
        }
    }
    return null;
}

function getDkimRecord($domain) {
    $selectors = ['default', 'google', 'k1', 'selector1', 'selector2'];
    foreach ($selectors as $selector) {
        $dkimDomain = $selector . '._domainkey.' . $domain;
        $records = dns_get_record($dkimDomain, DNS_TXT);
        if (!empty($records)) {
            return [
                'selector' => $selector,
                'record' => $records[0]['txt']
            ];
        }
    }
    return null;
}

function performLLMCheck($emailPrefix) {
    $prompt = <<<EOD
Du bekommst den Teil einer Email-Adresse vor dem @-Zeichen. Du bewertest, ob es eine persönliche Email
(z.B. dieter.ahnen) oder eine Massenaddresse ist (Email Verteiler oder ein Sammelpostfach geschickt,
z.B. pressestelle). Die Antwort kann entweder 'Person' oder 'Verteiler' sein. Bitte antworte im JSON Format mit den Feldern 'classification' und 'description', die Beschreibung muss mit der Klassifikation konsistent sein.

Email-Prefix für die Bewertung:
EOD;

    $yourApiKey = getenv('OPENAI_API_KEY');
    if (!$yourApiKey) {
        return [
            'classification' => 'unknown',
            'description' => 'OpenAI API key not configured'
        ];
    }

    try {
        $client = OpenAI::client($yourApiKey);

        $result = $client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt . $emailPrefix],
            ],
            'response_format' => [
                'type' => 'json_object'
            ]
        ]);

        $llmResponse = json_decode($result->choices[0]->message->content, true);
        
        return [
            'classification' => $llmResponse['classification'],
            'description' => $llmResponse['description']
        ];
    } catch (Exception $e) {
        return [
            'classification' => 'unknown',
            'description' => 'Error during LLM classification: ' . $e->getMessage()
        ];
    }
}

function emailExists($email, $domain) {
    // Get MX records
    getmxrr($domain, $mxhosts, $mxweight);
    
    if (empty($mxhosts)) {
        return ['exists' => false, 'reason' => 'No MX records found'];
    }
    
    // Try connecting to the first MX host
    $port = 25;
    $timeout = 5;
    $sock = @fsockopen($mxhosts[0], $port, $errno, $errstr, $timeout);
    
    if (!$sock) {
        return ['exists' => false, 'reason' => 'Could not connect to mail server'];
    }
    
    // SMTP conversation
    $response = fgets($sock, 4096);
    if (empty($response)) {
        fclose($sock);
        return ['exists' => false, 'reason' => 'No response from server'];
    }
    
    $cmds = [
        "HELO example.com\r\n",
        "MAIL FROM: <check@example.com>\r\n",
        "RCPT TO: <{$email}>\r\n",
        "QUIT\r\n"
    ];
    
    $exists = true;
    $reason = '';
    
    foreach ($cmds as $cmd) {
        fputs($sock, $cmd);
        $response = fgets($sock, 4096);
        
        // Check for error codes in response
        if (strpos($response, '550') !== false || 
            strpos($response, '553') !== false || 
            strpos($response, '501') !== false || 
            strpos($response, '504') !== false) {
            $exists = false;
            $reason = trim($response);
            break;
        }
    }
    
    fclose($sock);
    return ['exists' => $exists, 'reason' => $reason];
}

// Main execution logic
if (php_sapi_name() === 'cli') {
    $options = getopt('', ['email:', 'verify-existence::', 'serve::']);
    
    if (isset($options['serve'])) {
        // Start the built-in PHP server
        $command = 'php -S localhost:8080 ' . __FILE__;
        echo "Starting web server at http://localhost:8080\n";
        system($command);
        exit;
    }
    
    if (!isset($options['email'])) {
        die("Usage: php check.php --email your_email@example.com [--verify-existence] [--serve]\n");
    }

    $fullEmail = $options['email'];
    
    // Run all checks and output results in CLI format
    $results = performEmailCheck($fullEmail);
    
    // Display results in CLI format
    echo "=== EMAIL ANALYSIS RESULTS ===\n\n";
    echo "Email Address: {$results['email']}\n";
    echo "Domain Status: {$results['domain_status']}\n\n";

    echo "DNS RECORDS:\n";
    echo "------------\n";
    echo "MX Records:\n";
    if ($results['dns_checks']['mx']['exists']) {
        foreach ($results['dns_checks']['mx']['records'] as $record) {
            echo "- Priority: {$record['pri']}, Host: {$record['target']}\n";
        }
    } else {
        echo "- No MX records found\n";
    }

    echo "\nSPF Record:\n";
    echo $results['dns_checks']['spf'] ? "- {$results['dns_checks']['spf']}\n" : "- Not found\n";

    echo "\nDMARC Record:\n";
    echo $results['dns_checks']['dmarc'] ? "- {$results['dns_checks']['dmarc']}\n" : "- Not found\n";

    echo "\nDKIM Record:\n";
    if ($results['dns_checks']['dkim']) {
        echo "- Selector: {$results['dns_checks']['dkim']['selector']}\n";
        echo "- Record: {$results['dns_checks']['dkim']['record']}\n";
    } else {
        echo "- Not found\n";
    }

    echo "\nEMAIL CLASSIFICATION:\n";
    echo "--------------------\n";
    echo "Type: {$results['classification']['type']}\n";
    echo "Reasoning: {$results['classification']['reasoning']}\n";
} else {
    serveWebInterface();
}
