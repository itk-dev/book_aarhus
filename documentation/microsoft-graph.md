# Microsoft Graph

The application relies on Microsoft Graph to handle busy-intervals lookups and booking requests.

To enable this the following environment variables should be set in `.env.local`:

```text
###> App ###
MICROSOFT_GRAPH_TENANT_ID=""
MICROSOFT_GRAPH_CLIENT_ID=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_NAME=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_USERNAME=""
MICROSOFT_GRAPH_SERVICE_ACCOUNT_PASSWORD=""
###< App ###
```

There are several commands available to test requests in Microsoft Graph:

```text
app:test:graph-busy            Get busy intervals for resource
app:test:graph-create-booking  Create a booking
app:test:graph-user-booking    Get user booking for given UserId
```

eg.

```shell
docker compose exec phpfpm bin/console app:test:graph-create-booking
```
