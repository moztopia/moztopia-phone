# Example container

The single [server](server/README.md) runs Laravel's development server. Compose publishes `127.0.0.1:8080` and isolates services on `appnet`. This localhost example is explicitly exempt from cloudflared by the owner. Start/test/stop through the example scripts; no image contains the host's generated apps, vendor tree or wiki.

`appnet` uses `10.253.247.0/28`; `PHONE_EXAMPLE_SUBNET` can select another unused private CIDR when local routes conflict. Docker refuses overlapping networks. No existing network is pruned or changed.

by-dolly
