<?php
namespace Mojopollo\BingScraper;

use GuzzleHttp\Client;

/**
 * Mostly the communication with the guzzle client
 */
class Scraper implements ScraperInterface
{
    /**
     * Debug switch
     *
     * @var bool
     */
    public $debug = false;

    /**
     * Trace file path
     *
     * Will dump CURL's trace output to this file
     *
     * @var string
     */
    public $traceFile = null;

    public function debug($value)
    {
        // Set debug value
        $this->debug = (bool) $value;

        // Return instance
        return $this;
    }

    public function traceFile($value)
    {
        // Set debug value
        $this->traceFile = $value;

        // Return instance
        return $this;
    }

    public function openFile($filePath)
    {
        return fopen($filePath, 'a');
    }

    public function httpRequest($method, $uri, $options)
    {
        // Set any debug options
        $debugOptions = isset($this->traceFile) ? ['debug' => $this->openFile($this->traceFile)] : [];
        file_put_contents('./dump.txt', json_encode($this->traceFile));

        // Create guzzle client
        $client = new Client($debugOptions);

        // If no user-agent was provided, do not send one
        // Guzzle sends something like this as the User-Agent:
        // GuzzleHttp/6.2.0 curl/7.43.0 PHP/7.0.6
        if (!isset($options['headers']['User-Agent'])) {
            $options['headers']['User-Agent'] = null;
        }

        // Send request and return results
        return $client->request($method, $uri, $options);
    }

    public function httpGet($uri, $query = null, $headers = null)
    {
        return $this->request('GET', $uri, [
            'query' => $query,
            'headers' => $headers,
        ]);
    }

    public function httpResponse($response)
    {
        // Create results array
        $results = [
            'meta' => [
                'statusCode' => $response->getStatusCode(),
                'contentType' =>
                    isset($response->getHeader('Content-Type')[0])
                    ? $response->getHeader('Content-Type')[0]
                    : $response->getHeader('Content-Type'),
                'cookies' => $response->getHeader('Set-Cookie'),
                'error' => null,
                'count' => 0,
            ],
            'results' => [
            ],
        ];

        // Set debug info if debug is on
        if ($this->debug) {
            $results['meta']['debug'] = [
                'headers' => $response->getHeaders(),
                'body' => $response->getBody()->getContents(),
            ];
        }

        // Return results array
        return $results;
    }
}
