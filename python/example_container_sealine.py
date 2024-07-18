from containertrackingapi import ContainerTrackingApi

api_wrapper = ContainerTrackingApi(apikey='YOUR_API_KEY')

try:
    result = api_wrapper.container(container_number="MEDU6965343", sealine="MSCU")
    print(result)
except Exception as e:
    print(e)
