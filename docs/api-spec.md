# Kestrel Data — Organisation Profile API

**Version:** v1 (beta)
**Base URL:** `http://localhost:8000` (local stand-in — run `php -S localhost:8000 fake-api/server.php` from the repo root)

Kestrel Data provides company registration and officer data for UK organisations. This API is currently in **beta**.

## Authentication

All requests require an API key in the `X-Api-Key` header. Your key for this integration: `kd_test_9f3a71`.

Requests without a valid key receive `401 Unauthorized`.

## Rate limiting

Requests are limited to **30 per minute** per API key. Exceeding the limit returns `429 Too Many Requests` with a `Retry-After` header indicating the number of seconds to wait.

## Endpoints

### Get organisation profile

```
GET /v1/organisations/{orgId}
```

`orgId` is an 8-character registration number, e.g. `RC482913`.

**Response `200`:**

```json
{
  "org_id": "RC482913",
  "name": "Harlech Systems Ltd",
  "description": "Fixture description for organisation RC482913",
  "status": "active",
  "type": "private-limited",
  "incorporated_on": "2011-03-28",
  "dissolved_on": null,
  "registered_address": {
    "line1": "4 Dunraven Place",
    "line2": null,
    "town": "Bridgend",
    "postcode": "CF31 1JD",
    "country": "GB"
  },
  "sic_codes": ["62012", "62020"],
  "employee_band": "50-99",
  "last_updated": "2026-05-14T09:12:44Z"
}
```

`status` is one of `active`, `dormant`, `dissolved`, `liquidation`. Fields may be `null` where data is not held. Dates are ISO 8601 (dates are date-only; timestamps are UTC).

### List organisation officers

```
GET /v1/organisations/{orgId}/officers
```

**Response `200`:**

```json
{
  "org_id": "RC482913",
  "count": 2,
  "officers": [
    {
      "officer_id": "off_2291",
      "name": "GRIFFITHS, Eleri Mai",
      "description": "Fixture description for officer off_2291",
      "role": "director",
      "appointed_on": "2011-03-28",
      "resigned_on": null
    },
    {
      "officer_id": "off_5106",
      "name": "PRICE, Owen",
      "description": "Fixture description for officer off_5106",
      "role": "secretary",
      "appointed_on": "2015-11-02",
      "resigned_on": "2023-06-30"
    }
  ]
}
```

`role` is one of `director`, `secretary`, `llp-member`. Officer names are returned in registry format (`SURNAME, Forenames`).

The `description` fields are fixture markers rather than registry data. They
exist so local consumers can change a value and observe provider and cache
behaviour during development.

## Errors

The API uses conventional HTTP status codes. Error responses have a consistent body:

```json
{
  "error": {
    "code": "not_found",
    "message": "No organisation with id RC000000."
  }
}
```

| Status | Code | Meaning |
|---|---|---|
| 400 | `bad_request` | Malformed `orgId` |
| 401 | `unauthorized` | Missing or invalid API key |
| 404 | `not_found` | Unknown organisation |
| 429 | `rate_limited` | Rate limit exceeded — see `Retry-After` |
| 500 | `internal_error` | Something went wrong on our side |
| 503 | `unavailable` | Temporary maintenance or overload |

## Service status

The beta service targets 99% availability. Response times are typically under 200ms but are not guaranteed.

## Test data

The local stand-in holds 20 organisations. Useful IDs:

| orgId | Notes |
|---|---|
| `RC482913` | Active, full data |
| `RC115637` | Dissolved, `dissolved_on` set, no current officers |
| `RC900218` | Dormant, several `null` fields |
| `RC734561` | Active, 12 officers (largest officer list) |
| `RC000000` | Does not exist (guaranteed 404) |
