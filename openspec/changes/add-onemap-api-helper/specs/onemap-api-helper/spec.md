## Purpose

Provides applications with server-side OneMap API access for token generation, Singapore address search, WGS84 reverse geocoding, and SVY21 to WGS84 coordinate conversion while keeping credentials and tokens out of browser code.

## ADDED Requirements

### Requirement: Token generation and reuse
The package SHALL allow applications to obtain a OneMap access token using registered OneMap credentials and SHALL reuse a valid token for protected OneMap requests when credentials are configured.

#### Scenario: Generate token from configured credentials
- **WHEN** an application requests a token and no valid cached token or manual token is available
- **THEN** the package sends the configured email and password to `POST /api/auth/post/getToken`
- **AND** returns the decoded `access_token` and `expiry_timestamp` response fields

#### Scenario: Reuse valid cached token
- **WHEN** an application calls a protected OneMap operation and a cached token is still valid after applying the configured expiry buffer
- **THEN** the package uses the cached token without requesting a new token

#### Scenario: Refresh expired token
- **WHEN** an application calls a protected OneMap operation and the cached token is expired or inside the configured expiry buffer
- **THEN** the package requests a fresh token before calling the protected endpoint

#### Scenario: Use supplied token
- **WHEN** an application supplies a token for a protected OneMap operation
- **THEN** the package uses that token for the request instead of resolving credentials or cached tokens

#### Scenario: Missing credentials
- **WHEN** an application requests automatic token generation without configured or supplied credentials
- **THEN** the package fails before calling OneMap with an input/configuration error

### Requirement: Address search
The package SHALL support OneMap address search through `GET /api/common/elastic/search` with token authentication and the official `searchVal`, `returnGeom`, `getAddrDetails`, and `pageNum` query parameters.

#### Scenario: Search with default options
- **WHEN** an application searches for `200640` without custom options
- **THEN** the package calls `/api/common/elastic/search` with `searchVal=200640`, `returnGeom=Y`, `getAddrDetails=Y`, and `pageNum=1`
- **AND** includes an `Authorization` header containing a resolved OneMap token
- **AND** returns OneMap's decoded response array without renaming response fields

#### Scenario: Search without geometry
- **WHEN** an application searches with geometry disabled
- **THEN** the package sends `returnGeom=N` to OneMap

#### Scenario: Search without address details
- **WHEN** an application searches with address details disabled
- **THEN** the package sends `getAddrDetails=N` to OneMap

#### Scenario: Search with specific page
- **WHEN** an application searches with page number `2`
- **THEN** the package sends `pageNum=2` to OneMap

#### Scenario: Reject empty search text
- **WHEN** an application searches with an empty search value
- **THEN** the package fails before calling OneMap with an input validation error

#### Scenario: Reject invalid search page
- **WHEN** an application searches with a page number less than `1`
- **THEN** the package fails before calling OneMap with an input validation error

### Requirement: WGS84 reverse geocoding
The package SHALL support OneMap WGS84 reverse geocoding through `GET /api/public/revgeocode` using latitude and longitude coordinates, token authentication, and the official optional `buffer`, `addressType`, and `otherFeatures` query parameters.

#### Scenario: Reverse geocode browser coordinates
- **WHEN** an application reverse geocodes latitude `1.3254295` and longitude `103.9005321`
- **THEN** the package calls `/api/public/revgeocode` with `location=1.3254295,103.9005321`
- **AND** includes an `Authorization` header containing a resolved OneMap token
- **AND** returns OneMap's decoded response array without renaming response fields

#### Scenario: Reverse geocode with optional filters
- **WHEN** an application reverse geocodes coordinates with buffer `40`, address type `All`, and other features enabled
- **THEN** the package sends `buffer=40`, `addressType=All`, and `otherFeatures=Y` to OneMap

#### Scenario: Reverse geocode HDB addresses only
- **WHEN** an application reverse geocodes coordinates with address type `HDB`
- **THEN** the package sends `addressType=HDB` to OneMap

#### Scenario: Reject invalid latitude
- **WHEN** an application reverse geocodes with latitude outside `-90` to `90`
- **THEN** the package fails before calling OneMap with an input validation error

#### Scenario: Reject invalid longitude
- **WHEN** an application reverse geocodes with longitude outside `-180` to `180`
- **THEN** the package fails before calling OneMap with an input validation error

#### Scenario: Reject invalid buffer
- **WHEN** an application reverse geocodes with a buffer outside `0` to `500`
- **THEN** the package fails before calling OneMap with an input validation error

#### Scenario: Reject invalid address type
- **WHEN** an application reverse geocodes with an address type other than `All` or `HDB`
- **THEN** the package fails before calling OneMap with an input validation error

### Requirement: SVY21 to WGS84 coordinate conversion
The package SHALL support converting SVY21 coordinates to WGS84 through `GET /api/common/convert/3414to4326` using token authentication and the official `X` and `Y` query parameters.

#### Scenario: Convert valid SVY21 coordinates
- **WHEN** an application converts SVY21 X `29383.0069359146` and Y `32379.8329621008`
- **THEN** the package calls `/api/common/convert/3414to4326` with `X=29383.0069359146` and `Y=32379.8329621008`
- **AND** includes an `Authorization` header containing a resolved OneMap token
- **AND** returns OneMap's decoded response array

### Requirement: Secure configuration
The package SHALL expose configuration for OneMap base URL, credentials, optional manual token, token cache key, token expiry buffer, and HTTP timeout so applications can configure API access without hard-coding secrets.

#### Scenario: Configure via environment
- **WHEN** an application sets OneMap environment variables for credentials and timeout
- **THEN** the package reads those values through the published package configuration

#### Scenario: Manual token configured
- **WHEN** an application configures a manual OneMap token
- **THEN** protected OneMap operations can use that token without requiring configured email and password

#### Scenario: Credentials remain server-side
- **WHEN** an application uses the OneMap helper from server-side PHP code
- **THEN** the package does not require OneMap credentials or tokens to be exposed to browser JavaScript

### Requirement: Phase 1 API boundary
The package SHALL limit this capability to token generation, address search, WGS84 reverse geocoding, and SVY21 to WGS84 conversion for Phase 1.

#### Scenario: SVY21 is not part of the helper
- **WHEN** an application needs SVY21 reverse geocoding
- **THEN** this Phase 1 capability does not provide `/api/public/revgeocodexy` support

#### Scenario: Other OneMap APIs are excluded
- **WHEN** an application needs maps, static maps, routing, themes, planning area, or population APIs
- **THEN** this Phase 1 capability does not provide those APIs
