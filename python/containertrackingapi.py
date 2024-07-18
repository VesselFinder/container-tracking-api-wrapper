import re
import requests
import time
import json


class ContainerTrackingApi():
    _endpoint = 'https://container.vesselfinder.com/api/1.0'
    _validation_regex = re.compile(r'^\w{3}(U|J|Z|R)\d{7}$')

    def __init__(self, apikey):
        self.apikey = apikey

    # The function performs the following checks:
    # 1. Ensures the container number is 11 characters long.
    # 2. Validates the format: the first three characters are alphanumeric, the fourth is either 'U', 'J', 'Z', or 'R', followed by seven digits.
    # 3. Calculates a check digit using a custom formula and compares it with the last digit of the container number. The container number is considered valid if the numbers are matching.
    def validate_container_number(self, number):
        if len(number) != 11 or not self._validation_regex.match(number):
            return False
        
        alphabet = '0123456789A BCDEFGHIJK LMNOPQRSTU VWXYZ'
        number_from_formula = str(sum(alphabet.index(n) * pow(2, i) for i, n in enumerate(number[:-1])) % 11 % 10)

        return number_from_formula == number[-1]

    def call(self, method_url, **params):
        return requests.get("{}/{}".format(self._endpoint, method_url), params=params)

    def container(self, container_number, sealine=None, timeout = 60):
        # Validating the paramaters for Container method.
        if not self.validate_container_number(container_number):
            raise Exception("Invalid container number: {}".format(container_number))

        method_url = "container/{}/{}".format(self.apikey, container_number)
        if sealine is not None:
            sealine_len = len(sealine)
            if sealine_len < 2 or sealine_len > 4:
                raise Exception("Invalid sealine: {}".format(sealine))
            
            method_url += "/" + sealine
        
        if timeout is None or timeout == 0:
            response = self.call(method_url)
            return response.text
        elif timeout < 10:
            raise Exception("The timeout should be at least 10 seconds.")
        else:
            # The loop will run until the specified timeout is reached, ensuring the total waiting time stays within the limit.
            while timeout > 0:
                response = self.call(method_url)
                if response.status_code != 202:
                    return response.text
            
                # Wait 10 seconds before making the next request
                time.sleep(10)

                # Decrease the timeout by 10 seconds after the sleep
                timeout -= 10
            
            raise Exception("Request timed out.")
