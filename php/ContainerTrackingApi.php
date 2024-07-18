<?php

class ContainerTrackingApi
{
    private $endpoint = 'https://container.vesselfinder.com/api/1.0';
    private $apikey;

    public function __construct($apikey)
    {
        $this->apikey = $apikey;
    }

    /*
    The function performs the following checks:
    1. Clean the input by removing hyphens and spaces
    2. Ensures the container number is 11 characters long.
    3. Validates the format: the first three characters are alphanumeric, the fourth is either 'U', 'J', 'Z', or 'R', followed by seven digits.
    4. Calculates a check digit using a custom formula and compares it with the last digit of the container number. The container number is considered valid if the numbers are matching.
    */
    private function validateContainerNumber($number)
    {
        $container_num = preg_replace("/[\-\s]+/", "", $number);

        if (strlen($container_num) == 11) {
            $found = preg_match("/^\w{3}(U|J|Z|R)\d{7}$/", $container_num);
            if ($found == 1) {
                $sum = 0;
                $letters = str_split($container_num);

                for ($i = 0; $i < 10; $i++) {
                    $sum += strpos('0123456789A BCDEFGHIJK LMNOPQRSTU VWXYZ', $letters[$i]) * pow(2, $i);
                }

                return intval($letters[10]) == $sum % 11 % 10;
            }
        }

        return false;
    }

    private function call($methodUrl, $params = [])
    {
        $url = "{$this->endpoint}/{$methodUrl}";
        $query = http_build_query($params);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url . ($query == '' ? $query : '?' . $query));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        $responseInfo = curl_getinfo($ch);

        curl_close($ch);

        return [$responseInfo['http_code'], $response];
    }

    public function container($containerNumber, $sealine = null, $timeout = 60)
    {
        // Validating the parameters for Container method.
        if (!$this->validateContainerNumber($containerNumber)) {
            throw new Exception("Invalid container number: $containerNumber");
        }

        $methodUrl = "container/$this->apikey/$containerNumber";
        if ($sealine !== null) {
            $sealineLen = strlen($sealine);
            if ($sealineLen < 2 || $sealineLen > 4) {
                throw new Exception("Invalid sealine: $sealine");
            }

            $methodUrl .= "/$sealine";
        }

        if (!is_null($timeout) && gettype($timeout) == 'string') {
            throw new Exception('The timeout should be an integer.');
        }

        if (is_null($timeout) || $timeout == 0) {
            $responseCall = $this->call($methodUrl);

            return $responseCall[1];
        } elseif ($timeout < 10) {
            throw new Exception('The timeout should be at least 10 seconds.');
        } else {
            // The loop will run until the specified timeout is reached, ensuring the total waiting time stays within the limit.
            while ($timeout > 0) {
                list($responseHttpCode, $response) = $this->call($methodUrl);

                if (!is_null($responseHttpCode) && $responseHttpCode !== 202) {
                    return $response;
                }

                // Wait 10 seconds before making the next request
                sleep(10);
                
                // Decrease the timeout by 10 seconds after the sleep
                $timeout -= 10;
            }

            throw new Exception('Request timed out.');
        }
    }
}