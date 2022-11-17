# Microsoft Graph

The application relies on Microsoft Graph to handle busy-intervals lookups and booking requests.

To enable this the following environment variables should be set in `.env.local`:

```shell
###> App ###
MICROSOFT_GRAPH_TENANT_ID=""
MICROSOFT_GRAPH_CLIENT_ID=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_NAME=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_USERNAME=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_PASSWORD=""
###< App ###
```

A command is available to test requests in Microsoft Graph:

```shell
docker compose exec phpfpm bin/console app:graph:test
```
