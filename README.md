# Ukázková integrace s NIA IdP

Projekt naleznete na adrese: https://nia.otevrenamesta.cz/

## Nástroj pro validaci XML dle specifikace SAML 2.0

Více informací ve složce [`webroot/validate`](https://github.com/otevrenamesta/eidentita-example/tree/master/webroot/validate)

## (T)NIA certifikát

NIA na testovacím prostředí používá certifikáty platné typicky 1 rok, tyto certifikáty nejsou publikovány jinde než v metadatech samotného prostředí, tj.
  - (produkční) https://nia.eidentita.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml
  - (testovací) https://tnia.eidentita.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml

Certifikát je v PEM formě (base64 enkódované) v samotných metadatech v elementu X509Certificate (např. `EntityDescriptor > Signature > KeyInfo > X509Data > X509Certificate`)

Jsou tedy 2 možnosti jak certifikát získat
  1. vložit base64 formu do textového souboru, obklopit uvozením `-----BEGIN CERTIFICATE-----`/`-----END CERTIFICATE-----` a uložit jako `(t)nia.crt` (PEM format)
  2. použít oneliner pro extrakci `wget "https://tnia.eidentita.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml" -o /dev/null -O - | xmllint --pretty 1 - | grep X509Certificate | head -n1 | awk -F">" '{print $2}' | awk -F"<" '{print $1}' | base64 -d | openssl x509 -inform der -outform pem -text > tnia.crt`
