## Buhax - Account software for small companies

Accounting software for small companies without VAT and taxes.

Small companies, at least in austria, are free of VAT and taxes and just need to track income/outgoing as it is. This is an application where you can manage all that, including invoice and offer creation, month checks, reports, depreciations, and more...

### Installation

#### On windows it recommended to use a WSL ubuntu instance which is also linux in the end

```
mkdir buhax
cd buhax
wget https://github.com/NullixAT/buhax/releases/latest/download/docker-release.tar -O docker-release.tar
tar xf docker-release.tar
rm docker-release.tar  
cp config/env-default .env
docker-compose up -d --build
```