# PHP Wrapper
PHP wrapper for the Container Tracking API of [vesselfinder.com](vesselfinder.com) along with some examples.</br>
[You can find the full API documentation here.](https://containertest.vesselfinder.com/api/)

## Initialise API
On initialization you should set these parameters:
* **apikey**: Your personal key for accessing the API
```php
require('ContainerTrackingApi.php');

$apiWrapper = new ContainerTrackingApi('YOUR_API_KEY');
```

## API Calls
* (**GET**) ContainerTrackingApi.container(**container_number\***, sealine, timeout)

**\* Required parameters**

## Error Handling
All exceptions in the wrapper are using the default PHP Exception Class. All that changes is the error string in the raised exception.
```php
try {
    $result = $apiWrapper->container('SOME_CONTAINER_NUMBER');
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

## Usage of methods
See  all `examples_*` PHP files for full examples for the methods.

### Container

- ### How does Container method work

  When you first search for a container, its status is set to *`queued`*. This means the container is waiting in a queue to be accepted for processing. 

  1. **Queued Status:**
      - Initially, when a container is searched, it is assigned the status *`queued`*.
      - The container will remain in this state until it is accepted for processing.

  2. **Processing Status:**
      - Once the container is accepted, its status changes to *`processing`*.
      - Each subsequent search for any container will return the status *`processing`*, along with a message indicating which container is currently being processed. This continues until the request to the line is complete.

  3. **Completion Status:**
      - The final status will reflect whether the container request was completed successfully (*`success`*) or if there was an error (*`error`*).

- ### Example Workflow

  1. **Search for Container:** The status is *`queued`*.
  2. **Accepted for Processing:** The status changes to *`processing`*.
  3. **Search Again:** The status remains *`processing`* until the request is completed.
  4. **Completion:** The final status will be either *`success`* or *`error`*.

  This example workflow demonstrates the sequence of internal states and API interactions performed by the wrapper's logic.

- ### Using the `timeout` parameter

  The `timeout` parameter is used to control how long the function should wait before terminating its execution. If the specified `timeout` duration is reached, or if the HTTP response status code is different than *`202`* (the code returned for statuses *`queued`* and *`processing`*), the function will stop checking and return the current status.

  - **Purpose of** `timeout`:
    - To prevent the function from running indefinitely.
    - To automate the status checks, so the user doesn't have to manually search every time.

  - **Behavior:**
    - If `timeout=None` or `timeout=0`, the function will perform only one API call without any subsequent checks.
    - If a `timeout<10` seconds is provided, an exception will be raised.
    - If a `timeout>=10` seconds, the function will check the status at 10-second intervals until the specified time is reached.
    - If a `timeout` value is not a multiple of 10, the actual timeout will be rounded up to the next multiple of 10. For example, if a `timeout` of 15 seconds is specified, the actual `timeout` will be 20 seconds.

- ### Parameters

  - `container_number`:
    - **Type**: `string`
    - **Length**: 11
    - **Description**: Container number.
    - **Example**: *`"MEDU6965343"`*
  - `sealine`:
    - **Type**: `string`
    - **Min Length**: 2
    - **Max Length**: 4
    - **Description**: Standard Carrier Alpha Code (SCAC).
    - **Default**: *`"AUTO"`*
    - **Example**: *`"MSCU"`*
  - `timeout`:
    - **Type**: `integer`
    - **Description**: The maximum time (in seconds) to wait before terminating the function's execution.
    - **Default**: *`60`*
    - **Example**: *`120`*

- ### Example

  ```php
  $container = $apiWrapper->container('SOME_CONTAINER_NUMBER', 'SOME_SEALINE', SOME_TIMEOUT);
  ```