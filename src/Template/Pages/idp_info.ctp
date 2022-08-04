<?php
/**
 * @var $this AppView
 * @var $title string
 */

use App\View\AppView; ?>
<h1><?= $title ?></h1>

<p>NIA (poskytována organizací SZRČR) je ve smyslu SAML služeb IdP, tedy poskytovatel identit.

<p>Pro integraci lze využít následující standardy komunikace

<ol>
    <li>
        <a target="_blank" href="https://cs.wikipedia.org/wiki/Security_Assertion_Markup_Language"
           title="Security Assertion Markup Language - Wikipedia">SAML2 Core</a> /
        <a target="_blank" title="electronic IDentification, Authentication and trust Services - Wikipedia"
           href="https://cs.wikipedia.org/wiki/EIDAS">eIDAS</a>
    </li>
    <li>
        <a title="Web Services Federation Language - Wikipedia" target="_blank"
           href="https://en.wikipedia.org/wiki/WS-Federation">WS-Federation</a>
    </li>
</ol>

<p>Abychom se mohli integrovat s IdP, je třeba pochopit poskytované služby a rozhraní, k tomu slouží IdP metadata.
<p>Dle <a href="https://info.identitaobcana.cz/download/SeP_PriruckaKvalifikovanehoPoskytovatele.pdf">příručky
        SeP</a> jsou metadata na těchto adresách

<ol>
    <li>Testovací prostředí: <a target="_blank"
                                href="https://tnia.identitaobcana.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml">https://tnia.identitaobcana.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml</a>
    </li>
    <li>Produkční prostředí: <a target="_blank"
                                href="https://nia.identitaobcana.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml">https://nia.identitaobcana.cz/fpsts/FederationMetadata/2007-06/FederationMetadata.xml</a>
    </li>
</ol>

<h2>Ukázka XML metadat (testovací prostředí)</h2>
<pre><code class="xml">&lt;?xml version="1.0"?>
&lt;!-- Sekce s podpisem a kontrolním součtem obsahu dokumentu (rsa-sha256) dle specifikace xmldsig --&gt;

&lt;EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata" ID="_b931baf7-9318-49cc-be37-d347ecf24a44" entityID="urn:microsoft:cgg2010:fpsts">
  &lt;Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
    &lt;SignedInfo>
      &lt;CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
      &lt;SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
      &lt;Reference URI="#_b931baf7-9318-49cc-be37-d347ecf24a44">
        &lt;Transforms>
          &lt;Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
          &lt;Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
        &lt;/Transforms>
        &lt;DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
        &lt;DigestValue>C3CAUoTaZ59+kAthzBU2Puz65hrvzwcINxHxrBOuhwk=&lt;/DigestValue>
      &lt;/Reference>
    &lt;/SignedInfo>
    &lt;SignatureValue>ElTkmNlB0ybN4LRT33Vf/FO57WxuQHLA8tPg0tuMUcx3NkTUGQ2IkcQS+8raGkp2ExTXeywUAolerK5h9cbKclyjRB6d01zE5QbynRP1kVVsIapRDO2G22jljFwTXyJrQs1jtgoZPN/6flhVlG77JnBNjy/EZKY8swHfwTpniqqfwinfd6vvoyMLxUkKg4b8Aif6wHIjaYyanEQAvNZeWfqAB2yIo15gJmdK3y5jdZCoODm5lfeHnVx3dRgZQZo6M+zVS5UmPJAPX7W8cAZSso6JGu2YX3pC/TpcOnk6PXYVW9XXtpecmnIqTpSriDM8wnyw9EEkDQPMdMl7exygag==&lt;/SignatureValue>
    &lt;KeyInfo>
      &lt;X509Data>
        &lt;X509Certificate>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&lt;/X509Certificate>
      &lt;/X509Data>
    &lt;/KeyInfo>
  &lt;/Signature>

 &lt;!-- Metadata pro kofiguraci protistrany komunikující protokolem WS-Federation --&gt;

  &lt;RoleDescriptor xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:fed="http://docs.oasis-open.org/wsfed/federation/200706" xsi:type="fed:SecurityTokenServiceType" protocolSupportEnumeration="http://docs.oasis-open.org/wsfed/federation/200706">
    &lt;KeyDescriptor use="signing">
      &lt;KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
        &lt;X509Data>
          &lt;X509Certificate>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&lt;/X509Certificate>
        &lt;/X509Data>
      &lt;/KeyInfo>
    &lt;/KeyDescriptor>
    &lt;fed:TokenTypesOffered>
      &lt;fed:TokenType Uri="http://schemas.microsoft.com/ws/2006/05/identitymodel/tokens/Saml"/>
    &lt;/fed:TokenTypesOffered>
    &lt;fed:ClaimTypesOffered>
      &lt;auth:ClaimType xmlns:auth="http://docs.oasis-open.org/wsfed/authorization/200706" Uri="http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress">
        &lt;auth:DisplayName>Email address&lt;/auth:DisplayName>
        &lt;auth:Description>The email of the subject.&lt;/auth:Description>
      &lt;/auth:ClaimType>
    &lt;/fed:ClaimTypesOffered>
    &lt;fed:SecurityTokenServiceEndpoint>
      &lt;wsa:EndpointReference xmlns:wsa="http://www.w3.org/2005/08/addressing">
        &lt;wsa:Address>https://tnia.identitaobcana.cz/FPSTS/issue.svc&lt;/wsa:Address>
      &lt;/wsa:EndpointReference>
    &lt;/fed:SecurityTokenServiceEndpoint>
    &lt;fed:PassiveRequestorEndpoint>
      &lt;wsa:EndpointReference xmlns:wsa="http://www.w3.org/2005/08/addressing">
        &lt;wsa:Address>https://tnia.identitaobcana.cz/FPSTS/default.aspx&lt;/wsa:Address>
      &lt;/wsa:EndpointReference>
    &lt;/fed:PassiveRequestorEndpoint>
  &lt;/RoleDescriptor>

 &lt;!-- Metadata pro konfiguraci protistrany komunikující protokolem SAML2.0 dle eIDAS --&gt;

  &lt;IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    &lt;KeyDescriptor use="signing">
      &lt;KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
        &lt;X509Data>
          &lt;X509Certificate>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&lt;/X509Certificate>
        &lt;/X509Data>
      &lt;/KeyInfo>
    &lt;/KeyDescriptor>
    &lt;SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="https://tnia.identitaobcana.cz/FPSTS/saml2/basic"/>
    &lt;SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="https://tnia.identitaobcana.cz/FPSTS/saml2/basic"/>
    &lt;SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="https://tnia.identitaobcana.cz/FPSTS/saml2/basic"/>
  &lt;/IDPSSODescriptor>
&lt;/EntityDescriptor>

</code></pre>

<h2>Popis metadat</h2>
<ul>
    <li>Metadata obsahují konfiguraci jak pro klienta podporujícího specifikaci SAML2 Core, tak pro WS-Federation</li>
    <li><code style="background-color: #eee">/RoleDescriptor</code> je popisem služeb pro WS-Federation</li>
    <li><code style="background-color: #eee">/IDPSSODescriptor</code> je popisem služeb pro SAML2</li>
    <li><code style="background-color: #eee">/Signature</code> obsahuje SHA256 hash a podpis, příslušným certifikátem,
        celého dokumentu (dle <code style="background-color: #eee">/Signature/SignedInfo/Reference@URI</code>)
    </li>
    <li><code style="background-color: #eee">/Signature/KeyInfo/X509Data/X509Certificate</code> obsahuje Base64
        enkódovanou DER formu X509 certifikátu (v tomto případě certifikát pro elektronickou pečeť od CA PostSignum)
    </li>
</ul>

<aside>
    <p>
        Pro účely vysvětlení budeme používat SAML, tedy nás zajímá, že v metadatech jsou poskytnuty služby "Logout"
        (režim HTTP-Redirect) a "SignOn" (HTTP-Redirect i HTTP-POST) a URL adresy, na které máme uživatele přesměrovat
        nebo kam máme odeslat data
    </p>
</aside>

<h2>Dekódovaný certifikát z metadat</h2>
<pre><code class="x509">
Certificate:
    Data:
        Version: 3 (0x2)
        Serial Number: 22080864 (0x150ed60)
        Signature Algorithm: sha256WithRSAEncryption
        Issuer: C = CZ, organizationIdentifier = NTRCZ-47114983, O = "\C4\8Cesk\C3\A1 po\C5\A1ta, s.p.", CN = PostSignum Qualified CA 4
        Validity
            Not Before: Feb 19 08:41:42 2020 GMT
            Not After : Mar 10 08:41:42 2021 GMT
        Subject: C = CZ, organizationIdentifier = NTRCZ-72054506, O = Spr\C3\A1va z\C3\A1kladn\C3\ADch registr\C5\AF, CN = GG_FPSTS_TEST, serialNumber = S275730
        Subject Public Key Info:
            Public Key Algorithm: rsaEncryption
                RSA Public-Key: (2048 bit)
                Modulus:
                    00:b5:7a:1b:64:0e:cc:66:e1:13:08:bc:f6:40:2f:
                    c6:b1:ad:92:00:8a:c4:f6:f6:49:31:b2:2d:08:ed:
                    d0:22:8a:1d:9f:12:f5:87:70:6e:63:32:5f:21:75:
                    c3:b8:d2:1f:d1:54:e6:44:8c:81:b2:0b:3e:33:5c:
                    27:47:c2:a9:ae:14:90:91:a5:30:44:26:e3:e6:a0:
                    b6:23:20:57:5d:07:d2:a9:b5:1e:ca:25:9a:87:d4:
                    03:46:93:d6:de:09:b9:85:07:23:53:47:99:93:c6:
                    75:26:ee:5f:65:58:e7:c1:e2:44:bc:6e:63:e1:6b:
                    09:f7:12:e0:a0:cc:8d:a0:54:de:e7:7f:07:dd:3c:
                    fc:46:ec:74:be:05:4c:fc:f3:05:b1:01:28:92:66:
                    10:5b:0d:9c:cc:dd:72:fe:cd:19:20:ac:2b:3b:0a:
                    ea:84:3f:8f:d6:9c:62:5f:44:f4:49:84:f2:11:c4:
                    94:40:79:99:bf:32:77:15:51:8f:2d:5c:a2:f1:eb:
                    9c:f3:ef:09:72:95:2f:c9:c3:88:f4:0a:73:3c:d4:
                    7a:dc:ee:b8:79:05:81:47:95:0d:5e:df:b4:47:e4:
                    b7:96:2b:9f:c2:74:ad:5b:7f:ab:ba:30:f1:44:0a:
                    79:fd:b7:29:7b:a4:08:c7:ea:0f:c9:41:4f:88:83:
                    17:51
                Exponent: 65537 (0x10001)
        X509v3 extensions:
            X509v3 Certificate Policies:
                Policy: 2.23.134.1.4.1.18.200
                  User Notice:
                    Explicit Text: Tento kvalifikovany certifikat pro elektronickou pecet byl vydan v souladu s narizenim EU c. 910/2014.This is a qualified certificate for electronic seal according to Regulation (EU) No 910/2014.
                  CPS: http://www.postsignum.cz
                Policy: 0.4.0.194112.1.1

            qcStatements:
                0..0......F..0j.....F..0`0..(https://www.postsignum.cz/pds/pds_en.pdf..en0..(https://www.postsignum.cz/pds/pds_cs.pdf..cs0......F..0......F...
            Authority Information Access:
                CA Issuers - URI:http://crt.postsignum.cz/crt/psqualifiedca4.crt
                OCSP - URI:http://ocsp.postsignum.cz/OCSP/QCA4/

            X509v3 Key Usage: critical
                Digital Signature, Non Repudiation, Key Encipherment
            X509v3 Extended Key Usage:
                E-mail Protection, 1.3.6.1.4.1.311.10.3.12
            X509v3 Authority Key Identifier:
                keyid:0F:28:7C:3E:36:00:38:10:50:AE:3D:B8:21:97:8B:F7:60:5C:61:78

            X509v3 CRL Distribution Points:

                Full Name:
                  URI:http://crl.postsignum.cz/crl/psqualifiedca4.crl

                Full Name:
                  URI:http://crl2.postsignum.cz/crl/psqualifiedca4.crl

                Full Name:
                  URI:http://crl.postsignum.eu/crl/psqualifiedca4.crl

            X509v3 Subject Key Identifier:
                F2:63:08:3D:8B:27:C1:44:A2:68:DD:75:B7:A6:6E:F5:70:0D:78:F6
    Signature Algorithm: sha256WithRSAEncryption
         26:85:8d:c6:8e:74:fd:18:8e:2a:26:00:f7:e3:99:fb:7f:70:
         24:ea:cc:7f:a3:f5:12:84:93:5c:f2:86:80:54:87:b4:18:09:
         e5:7e:c2:dd:84:9f:27:0a:c2:6d:b8:01:8c:57:54:e0:71:09:
         39:07:93:97:6c:ea:26:58:7d:0e:aa:d4:c7:30:2e:2d:b2:8a:
         6c:c7:43:9f:c3:12:58:18:d2:6f:61:1a:43:6c:ff:a0:1a:02:
         0a:d4:7f:4e:13:dc:e6:a1:f7:d2:4d:bd:33:f0:91:12:cb:53:
         82:70:53:3c:1c:22:27:7d:cc:be:c3:d8:ec:8d:60:e9:61:e0:
         66:99:9a:e7:91:11:2a:f7:6c:6a:fa:d2:fd:3b:d7:7a:5e:7f:
         4d:75:99:91:cd:04:3a:99:fa:aa:7d:57:21:2c:03:d4:d4:67:
         fb:b9:38:9f:ec:50:f3:50:34:47:5f:d7:b3:f6:4a:39:9b:21:
         3f:26:9b:22:83:81:d7:5c:3b:2d:7d:ca:b4:9a:69:a6:7e:7e:
         82:bd:02:79:0c:c2:e3:cd:9f:e4:8d:ec:9a:09:2b:d8:f5:0e:
         0e:33:ac:0b:b4:9c:78:22:ea:55:a8:0f:e8:ed:af:c2:05:cb:
         0c:dd:17:de:a4:53:f9:45:3a:88:29:7c:9b:c1:80:de:2a:1f:
         c8:d8:97:0a:6d:d9:1e:19:e2:ed:d1:de:b2:75:b4:f4:11:63:
         26:40:b3:62:c9:fa:08:ce:16:f1:98:36:32:dc:f7:10:ac:d7:
         11:84:b5:5f:45:d9:e6:e9:17:89:ee:75:cd:f8:f0:bc:4b:2c:
         2d:57:28:d5:74:8e:9c:4f:9c:5b:df:99:22:92:1c:59:47:26:
         15:ee:58:37:70:13:fb:f6:7f:19:55:14:78:c5:db:52:2e:8f:
         78:a3:0f:b7:18:fa:83:75:5d:c7:af:16:38:89:76:c0:8b:86:
         ec:5b:61:3f:2e:21:79:3a:55:8c:de:e7:9e:23:e9:3d:bb:78:
         ea:c5:61:ca:3c:0f:b4:10:f3:4a:8a:05:85:ef:a5:87:84:5a:
         32:a3:33:20:c2:89:ac:7d:0f:c1:9e:a9:de:ef:ef:cb:6f:b1:
         4c:c0:ec:a6:30:3e:0d:b9:23:3a:28:ab:e4:bd:09:a7:3b:08:
         b0:7e:b2:84:32:e4:be:5c:91:4c:e1:a4:30:55:47:60:0b:8b:
         4b:9c:f5:eb:4b:ac:33:2c:16:25:17:27:ca:2a:2b:89:4c:67:
         ea:b7:8a:aa:fc:89:56:ec:11:ef:54:15:da:38:67:b8:88:c1:
         db:77:58:4c:9a:a8:d2:bc:01:72:b9:4c:7c:89:83:6a:d2:d7:
         e5:3b:39:4c:83:77:d4:c7
</code></pre>
