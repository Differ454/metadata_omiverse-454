<?php

if (empty($_SESSION['viewNSDomainDetails'])) {
    echo '<script>location.replace("/")</script>';
    die();
}


$domain = $_SESSION['viewNSDomainDetails'];
if (empty($_SESSION['NSDomainDetailsView'])) {
    $_SESSION['NSDomainDetailsView'] = "NSLOOKUP";

    $checkCloudflareStatusQuery = mysqli_query($conn, "SELECT * FROM $table_cloudflare_zones WHERE name = '$domain' && isDeleted != 1");
    if ($checkCloudflareStatusQuery) {
        if (mysqli_num_rows($checkCloudflareStatusQuery) > 0) {
            $checkCloudflareStatusResult = mysqli_fetch_array($checkCloudflareStatusQuery);
            if ($checkCloudflareStatusResult['status'] == 'active') {
                $_SESSION['NSDomainDetailsView'] = "CLOUDFLARE";
            }
        }
    }
}



echo '<div class="popupHidden">Copied to clipboard</div>';

$checkIfCloudflareTokenIsSetQuery = mysqli_query($conn, "SELECT * FROM $table_tokens WHERE name = 'cloudflare'");
if ($checkIfCloudflareTokenIsSetQuery) {
    if (mysqli_num_rows($checkIfCloudflareTokenIsSetQuery) > 0) {
        echo '<label for="radio"><form name="" method="post">';


        echo '<input name="NSDomainDetailsView" onclick="displayLoader()" type="radio" value="NSLOOKUP"';
        if ($_SESSION['NSDomainDetailsView'] == 'NSLOOKUP') {
            echo 'checked="checked"';
        }
        echo 'onchange="this.form.submit()"> NSLOOKUP';

        $checkIfDomainUsesCloudflareQuery = mysqli_query($conn, "SELECT * FROM $table_cloudflare_zones WHERE name = '$domain' && isDeleted != 1");
        if ($checkIfDomainUsesCloudflareQuery) {
            if (mysqli_num_rows($checkIfDomainUsesCloudflareQuery) > 0) {


                if ($_SESSION['NSDomainDetailsView'] == 'ADD2CLOUDFLARE') {
                    $_SESSION['NSDomainDetailsView'] = 'CLOUDFLARE';
                }


                echo '<input name="NSDomainDetailsView" onclick="displayLoader()" type="radio"  value="CLOUDFLARE"';
                if ($_SESSION['NSDomainDetailsView'] == 'CLOUDFLARE') {
                    echo 'checked="checked"';
                }


                echo 'onchange="this.form.submit()"> CLOUDFLARE';

                echo '</form></label>';
            } else {
                if ($_SESSION['NSDomainDetailsView'] == 'CLOUDFLARE') {
                    $_SESSION['NSDomainDetailsView'] = 'ADD2CLOUDFLARE';
                }
                echo '<input name="NSDomainDetailsView" onclick="displayLoader()" type="radio"  value="ADD2CLOUDFLARE"';
                if ($_SESSION['NSDomainDetailsView'] == 'ADD2CLOUDFLARE') {
                    echo 'checked="checked"';
                }
                echo 'onchange="this.form.submit()"> Add to Cloudflare';

                echo '</form></label>';
            }
        }
    } else {
        $_SESSION['NSDomainDetailsView'] = 'NSLOOKUP';
    }
}





if ($_SESSION['NSDomainDetailsView'] == 'NSLOOKUP') {


    $DNS_SOA = dns_get_record($domain, DNS_SOA);
    $DNS_SOA = $DNS_SOA[0];
    $DNS_SOA_EMAIL_ARRAY = explode(".", $DNS_SOA['rname']);
    $DNS_SOA_EMAIL = $DNS_SOA_EMAIL_ARRAY[0] . '@' . $DNS_SOA_EMAIL_ARRAY[1] . '.' . $DNS_SOA_EMAIL_ARRAY[2];
    $DNS_SOA_EMAIL = $DNS_SOA_EMAIL ?: 'N/A';
    $DNS_SOA_TTL = $DNS_SOA['ttl'] ?: 'N/A';
    $DNS_SOA_CLASS = $DNS_SOA['class'] ?: 'N/A';


    $DNS_A = dns_get_record($domain, DNS_A);
    $DNS_A_TARGET = "";
    foreach ($DNS_A as $A) {
        $DNS_A_TARGET .= "$A[ip]</br>";
    }
    $DNS_A = $DNS_A[0];
    $DNS_A_TARGET = $DNS_A_TARGET ?: 'N/A';
    $DNS_A_TTL = $DNS_A['ttl'] ?: 'N/A';
    $DNS_A_CLASS = $DNS_A['class'] ?: 'N/A';


    $DNS_AAAA = dns_get_record($domain, DNS_AAAA);
    $DNS_AAAA_TARGET = "";
    foreach ($DNS_AAAA as $AAAA) {
        $DNS_AAAA_TARGET .= "$AAAA[ipv6]</br>";
    }
    $DNS_AAAA = $DNS_AAAA[0];

    if ($DNS_A_TARGET == 'N/A') {
        $DNS_AAAA_TARGET = $DNS_AAAA_TARGET ?: 'N/A';
    } else {
        $DNS_AAAA_TARGET = $DNS_AAAA_TARGET ?: '<span style="font-weight:bold;">Suggestion</span></br> Enable IPv6';
    }




    $DNS_AAAA_TTL = $DNS_AAAA['ttl'] ?: 'N/A';
    $DNS_AAAA_CLASS = $DNS_AAAA['class'] ?: 'N/A';


    $DNS_NS = dns_get_record($domain, DNS_NS);
    asort($DNS_NS);
    $DNS_NS_TARGET = "";
    foreach ($DNS_NS as $NS) {
        $DNS_NS_TARGET .= "$NS[target]</br>";
    }
    $DNS_NS = $DNS_NS[0];
    $DNS_NS_TARGET = $DNS_NS_TARGET ?: 'N/A';
    $DNS_NS_TTL = $DNS_NS['ttl'] ?: 'N/A';
    $DNS_NS_CLASS = $DNS_NS['class'] ?: 'N/A';


    $DNS_MX = dns_get_record($domain, DNS_MX);

    usort($DNS_MX, function ($a, $b) {
        return $a['pri'] > $b['pri'];
    });

    $DNS_MX_TARGET = "";
    foreach ($DNS_MX as $MX) {
        $DNS_MX_TARGET .= "$MX[target]</br>";
    }
    $DNS_MX = $DNS_MX[0];
    $DNS_MX_TARGET = $DNS_MX_TARGET ?: 'N/A';
    $DNS_MX_TTL = $DNS_MX['ttl'] ?: 'N/A';
    $DNS_MX_CLASS = $DNS_MX['class'] ?: 'N/A';


    $DNS_TXT = dns_get_record($domain, DNS_TXT);
    $DNS_TXT_TARGET = "";
    $DNS_DMARC_TARGET = "";
    $DNS_SPF_TARGET = "";
    foreach ($DNS_TXT as $TXT) {
        if (strpos($TXT['txt'], 'v=spf1') !== FALSE) {
            $DNS_SPF_TARGET .= $TXT['txt'];
        } else {
            $DNS_TXT_TARGET .= $TXT['txt'];
        }
    }
    $DNS_TXT = $DNS_TXT[0];


    if (!empty($DNS_SPF_TARGET)) {
        $DNS_SPF_TARGET = str_replace(" ", "</br>", $DNS_SPF_TARGET);
        $DNS_SPF_TTL = $DNS_TXT['ttl'] ?: 'N/A';
        $DNS_SPF_CLASS = $DNS_TXT['class'] ?: 'N/A';
    }

    if (strpos($DNS_MX_TARGET, 'mail.protection.outlook.com') !== false) {
        $DNS_SPF_SUGGESTION = '
        <span style="cursor:pointer;" title="Copy to clipboard">
        <span style="font-weight:bold;">Suggestion</span></br>
        <span class="DNS_SPF_SUGGESTION_1" onclick="copy2Clipboard(`DNS_SPF_SUGGESTION_1`)">v=spf1</br>
        include:spf.protection.outlook.com</br>
        -all</span></span>';
    } else {
        $DNS_SPF_SUGGESTION = '
        <span style="font-weight:bold;">Suggestion</span></br>
        Enable SPF';
    }


    $DNS_SPF_TARGET = $DNS_SPF_TARGET ?: $DNS_SPF_SUGGESTION;
    $DNS_SPF_TTL = $DNS_SPF_TTL ?: 'N/A';
    $DNS_SPF_CLASS = $DNS_SPF_CLASS ?: 'N/A';


    if (!empty($DNS_TXT_TARGET)) {
        $DNS_TXT_TARGET = str_replace(" ", "</br>", $DNS_TXT_TARGET);
        $DNS_TXT_TTL = $DNS_TXT['ttl'] ?: 'N/A';
        $DNS_TXT_CLASS = $DNS_TXT['class'] ?: 'N/A';
    }
    $DNS_TXT_TARGET = $DNS_TXT_TARGET ?: 'N/A';
    $DNS_TXT_TTL = $DNS_TXT_TTL ?: 'N/A';
    $DNS_TXT_CLASS = $DNS_TXT_CLASS ?: 'N/A';


    $DNS_DMARC = dns_get_record('_dmarc.' . $domain, DNS_TXT);
    $DNS_DMARC = $DNS_DMARC[0];

    $DNS_DMARC_TARGET .= $DNS_DMARC['txt'];



    if (!empty($DNS_DMARC_TARGET)) {
        $DNS_DMARC_TARGET = str_replace(" ", "</br>", $DNS_DMARC_TARGET);
        $DNS_DMARC_TTL = $DNS_DMARC['ttl'] ?: 'N/A';
        $DNS_DMARC_CLASS = $DNS_DMARC['class'] ?: 'N/A';
    }


    $DNS_DMARC_SUGGESTION = '
    <span style="cursor:pointer;" title="Copy to clipboard">
    <span style="font-weight:bold;">Suggestion</span></br>
    <span class="DNS_DMARC_SUGGESTION_1" onclick="copy2Clipboard(`DNS_DMARC_SUGGESTION_1`)">_dmarc.' . $domain . '</span></br>
    TXT</br>
    <span class="DNS_DMARC_SUGGESTION_2" onclick="copy2Clipboard(`DNS_DMARC_SUGGESTION_2`)">v=DMARC1;</br> p=reject;</br>
    sp=reject;</br> adkim=r;</br>
    aspf=r;</br> fo=1;</br>
    rf=afrf;</br> rua=mailto:dmarc@cloudpit.dk;</br>
    ruf=mailto:dmarc@cloudpit.dk;</br> pct=100;</br>
    ri=86400</span>
    </span>';

    $DNS_DMARC_SUGGESTION_IN_USE = false;
    if (empty($DNS_DMARC_TARGET)) {
        $DNS_DMARC_TARGET = $DNS_DMARC_SUGGESTION;
    } else {

        if (
            strpos($DNS_DMARC_TARGET, "v=") === false || strpos($DNS_DMARC_TARGET, "p=") === false || strpos($DNS_DMARC_TARGET, "sp=") === false || strpos($DNS_DMARC_TARGET, "adkim=") === false ||
            strpos($DNS_DMARC_TARGET, "aspf=") === false || strpos($DNS_DMARC_TARGET, "fo=") === false || strpos($DNS_DMARC_TARGET, "rf=") === false || strpos($DNS_DMARC_TARGET, "rua=") === false ||
            strpos($DNS_DMARC_TARGET, "ruf=") === false || strpos($DNS_DMARC_TARGET, "pct=") === false || strpos($DNS_DMARC_TARGET, "ri=") === false
        ) {
            $DNS_DMARC_TARGET .= '</br></br>' . $DNS_DMARC_SUGGESTION;
        }
    }
    $DNS_DMARC_TTL = $DNS_DMARC_TTL ?: 'N/A';
    $DNS_DMARC_CLASS = $DNS_DMARC_CLASS ?: 'N/A';





    $DNS_DKIM = dns_get_record("selector1._domainkey." . $domain, DNS_CNAME);
    $DNS_DKIM_TARGET = "";
    foreach ($DNS_DKIM as $DKIM) {
        $DKIM_LOOKUP = dns_get_record($DKIM['target'], DNS_TXT);
        foreach ($DKIM_LOOKUP as $innerDKIM) {
            $DNS_DKIM_TARGET .= "$innerDKIM[txt]</br>";
        }
    }

    if (empty($DNS_DKIM_TARGET)) {
        $DNS_DKIM = dns_get_record("selector2._domainkey." . $domain, DNS_CNAME);
        foreach ($DNS_DKIM as $DKIM) {
            $DKIM_LOOKUP = dns_get_record($DKIM['target'], DNS_TXT);
            foreach ($DKIM_LOOKUP as $innerDKIM) {
                $DNS_DKIM_TARGET .= "$innerDKIM[txt]</br>";
            }
        }
    }


    $explodedDomain = explode(".", $domain);

    $selectorDomainArray = explode(".", $DNS_MX_TARGET);
    $selectorDomain = $selectorDomainArray[0];



    $getTenantDomainQuery = mysqli_query($conn, "SELECT * FROM $table_microsoftdomains WHERE domain LIKE '%$explodedDomain[0]%' && domain LIKE '%onmicrosoft%'");
    if ($getTenantDomainQuery) {
        if (mysqli_num_rows($getTenantDomainQuery) > 0) {
            $getTenantDomainResult = mysqli_fetch_array($getTenantDomainQuery);
            $DNS_DKIM_SUGGESTION = '
            <span style="cursor:pointer;" title="Copy to clipboard">
            <span style="font-weight:bold;">Suggestion</span></br> 
            <span class="DNS_DKIM_SUGGESTION_1_1" onclick="copy2Clipboard(`DNS_DKIM_SUGGESTION_1_1`)">selector1._domainkey.' . $domain . '</span> </br>
            CNAME </br>
            <span class="DNS_DKIM_SUGGESTION_1_2" onclick="copy2Clipboard(`DNS_DKIM_SUGGESTION_1_2`)">selector1-' . $selectorDomain . '._domainkey.' . $getTenantDomainResult['domain'] . '</span></br> </br> 

            <span class="DNS_DKIM_SUGGESTION_2_1" onclick="copy2Clipboard(`DNS_DKIM_SUGGESTION_2_1`)">selector2._domainkey.' . $domain . '</span> </br>
            CNAME </br>
            <span class="DNS_DKIM_SUGGESTION_2_2" onclick="copy2Clipboard(`DNS_DKIM_SUGGESTION_2_2`)">selector2-' . $selectorDomain . '._domainkey.' . $getTenantDomainResult['domain'] . '</span></span>';
        } else {

            $getTenantIDQuery = mysqli_query($conn, "SELECT * FROM $table_microsoftdomains WHERE domain LIKE '%$explodedDomain[0]%'");
            if ($getTenantIDQuery) {
                $getTenantIDResult = mysqli_fetch_array($getTenantIDQuery);

                $lookupTenantDomainQuery = mysqli_query($conn, "SELECT * 
            FROM $table_microsoftdomains
            WHERE tenantID = '$getTenantIDResult[tenantID]' && domain LIKE '%onmicrosoft%'");

                if ($lookupTenantDomainQuery) {
                    $lookupTenantDomainResult = mysqli_fetch_array($lookupTenantDomainQuery);

                    $DNS_DKIM_SUGGESTION = '
                    <span style="cursor:pointer;" title="Copy to clipboard">
                    <span style="font-weight:bold;">Suggestion</span></br> 
                    <span class="DNS_DKIM_SUGGESTION_1_1" onclick="copy2Clipboard(`DNS_DKIM_SUGGESTION_1_1`)">selector1._domainkey.' . $domain . '</span> </br>
                    CNAME </br>
                    <span class="DNS_DKIM_SUGGESTION_1_2" onclick="copy2Clipboard(`DNS_DKIM_SUGGESTION_1_2`)">selector1-' . $selectorDomain . '._domainkey.' . $lookupTenantDomainResult['domain'] . '</span></br> </br>


                    <span class="DNS_DKIM_SUGGESTION_2_1" onclick="copy2Clipboard(`DNS_DKIM_SUGGESTION_2_1`)">selector2._domainkey.' . $domain . '</span> </br>
                    CNAME </br>
                    <span class="DNS_DKIM_SUGGESTION_2_2" onclick="copy2Clipboard(`DNS_DKIM_SUGGESTION_2_2`)">selector2-' . $selectorDomain . '._domainkey.' . $lookupTenantDomainResult['domain'] . '</span></span>';
                }
            }
        }
    }

    if (strpos($DNS_MX_TARGET, 'mail.protection.outlook.com') !== false) {
        $DNS_DKIM_SUGGESTION = $DNS_DKIM_SUGGESTION;
    } else {
        $DNS_DKIM_SUGGESTION = '<span style="font-weight:bold;">Suggestion</span></br>
        Enable DKIM';
    }


    $DNS_DKIM = $DNS_DKIM[0];
    $DNS_DKIM_TTL = $DNS_DKIM['ttl'] ?: 'N/A';
    $DNS_DKIM_CLASS = $DNS_DKIM['class'] ?: 'N/A';


    if (is_null($DKIM_LOOKUP)) {

        $DNS_DKIM_TARGET = $DNS_DKIM_TARGET ?: $DNS_DKIM_SUGGESTION;
    } elseif (count($DKIM_LOOKUP) > 0) {

        $DNS_DKIM_TARGET = $DNS_DKIM_TARGET ?: $DNS_DKIM_SUGGESTION;
    } else {

        $DNS_DKIM_TARGET = '
        <span style="font-weight:bold;">Enable DKIM by using one of the following links below</span></br>
        <a href="https://security.microsoft.com/dkimv2" target="_blank">https://security.microsoft.com/dkimv2</a> </br>
        <a href="https://protection.office.com/dkimv2" target="_blank">https://protection.office.com/dkimv2</a>';
    }


    if (strpos($DNS_DKIM_TARGET, 'v=spf') !== false || strpos($DNS_DKIM_TARGET, 'MS=ms') !== false) {
        $DNS_DKIM_TARGET = "<span style='font-weight:bold;'>It looks like DKIM is misconfigured..</span></br></br> $DNS_DKIM_SUGGESTION";
    }






    echo '
    <table>
        <thead>
            <tr>
                <th width="100" align="left" scope="col" >Type</th>
                <th width="100" align="left" scope="col" >Class</th>
                <th align="left" scope="col" >TTL</th>
                <th align="right" scope="col" >Target</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Type"><span class="domainTypeFormat domainTypeSOA">SOA</span></td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Class">' . $DNS_SOA_CLASS . '</td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="TTL">' . $DNS_SOA_TTL . '</td>
                <td style="vertical-align:top;" align="right" scope="row" data-label="Target">
                Email: ' . $DNS_SOA_EMAIL . '</br>
                Serial: ' . $DNS_SOA['serial'] . '</br>
                Refresh: ' . $DNS_SOA['refresh'] . '</br>
                Retry: ' . $DNS_SOA['retry'] . '</br>
                Expire: ' . $DNS_SOA['expire'] . '</br>
                Minimum: ' . $DNS_SOA['minimum-ttl'] . '
                </td>
            </tr>

            <tr>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Type"><span class="domainTypeFormat domainTypeA">A</span></td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Class">' . $DNS_A_CLASS . '</td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="TTL">' . $DNS_A_TTL . '</td>
                <td style="vertical-align:top;" align="right" scope="row" data-label="Target">' . $DNS_A_TARGET . '</td>
            </tr>

            <tr>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Type"><span class="domainTypeFormat domainTypeAAAA">AAAA</span></td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Class">' . $DNS_AAAA_CLASS . '</td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="TTL">' . $DNS_AAAA_TTL . '</td>
                <td style="vertical-align:top;" align="right" scope="row" data-label="Target">' . $DNS_AAAA_TARGET . '</td>
            </tr>

            <tr>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Type"><span class="domainTypeFormat domainTypeNS">NS</span></td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Class">' . $DNS_NS_CLASS . '</td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="TTL">' . $DNS_NS_TTL . '</td>
                <td style="vertical-align:top;" align="right" scope="row" data-label="Target">' . $DNS_NS_TARGET . '</td>
            </tr>

            <tr>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Type"><span class="domainTypeFormat domainTypeMX">MX</span></td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Class">' . $DNS_MX_CLASS . '</td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="TTL">' . $DNS_MX_TTL . '</td>
                <td style="vertical-align:top;" align="right" scope="row" data-label="Target">' . $DNS_MX_TARGET . '</td>
            </tr>

            <tr>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Type"><span class="domainTypeFormat domainTypeTXT">TXT</span></td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Class">' . $DNS_TXT_CLASS . '</td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="TTL">' . $DNS_TXT_TTL . '</td>
                <td style="vertical-align:top;" align="right" scope="row" data-label="Target">' . $DNS_TXT_TARGET . '</td>
            </tr>

            <tr>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Type"><span class="domainTypeFormat domainTypeSPF">SPF</span></td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Class">' . $DNS_SPF_CLASS . '</td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="TTL">' . $DNS_SPF_TTL . '</td>
                <td style="vertical-align:top;" align="right" scope="row" data-label="Target">' . $DNS_SPF_TARGET . '</td>
            </tr>

            <tr>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Type"><span class="domainTypeFormat domainTypeDMARC">DMARC</span></td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Class">' . $DNS_DMARC_CLASS . '</td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="TTL">' . $DNS_DMARC_TTL . '</td>
                <td style="vertical-align:top;" align="right" scope="row" data-label="Target">' . $DNS_DMARC_TARGET . '</td>
            </tr>

            <tr>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Type"><span class="domainTypeFormat domainTypeDKIM">DKIM</span></td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Class">' . $DNS_DKIM_CLASS . '</td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="TTL">' . $DNS_DKIM_TTL . '</td>
                <td style="vertical-align:top;word-break:break-word;" align="right" scope="row" data-label="Target">' . $DNS_DKIM_TARGET . '</td>
            </tr>';



    $DNS_DNSSEC_TARGET = "";
    $DNS_DNSSEC_CLASS = "";
    $getDNSSECQuery = mysqli_query($conn, "SELECT * FROM `system-nslookup` WHERE Domain = '$domain' && Type = 'DNSKEY'");
    if ($getDNSSECQuery) {
        while ($getDNSSECResult = mysqli_fetch_array($getDNSSECQuery)) {
            $DNS_DNSSEC_TARGET .= $getDNSSECResult['Key'];
            $DNS_DNSSEC_TTL = $getDNSSECResult['TTL'];
            if (!empty($DNS_DNSSEC_TARGET)) {
                $DNS_DNSSEC_CLASS = "IN";
            }
        }
    }
    $DNS_DNSSEC_CLASS = $DNS_DNSSEC_CLASS ?: 'N/A';
    $DNS_DNSSEC_TARGET = $DNS_DNSSEC_TARGET ?: '<span style="font-weight:bold;">Suggestion</span></br> Enable DNSSEC';
    $DNS_DNSSEC_TTL = $DNS_DNSSEC_TTL ?: 'N/A';


    echo '

            <tr>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Type"><span class="domainTypeFormat domainTypeDNSSEC">DNSSEC</span></td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="Class">' . $DNS_DNSSEC_CLASS . '</td>
                <td style="vertical-align:top;" align="left" scope="row" data-label="TTL">' . $DNS_DNSSEC_TTL . '</td>
                <td style="vertical-align:top;word-break:break-word;" align="right" scope="row" data-label="Target">' . $DNS_DNSSEC_TARGET . '</td>
            </tr>


        </tbody>
    </table>';
} elseif ($_SESSION['NSDomainDetailsView'] == 'CLOUDFLARE') {

    $getDNSRecordsQuery = mysqli_query($conn, "SELECT CFR.* 
    FROM $table_cloudflare_zones AS CFZ
    INNER JOIN $table_cloudflare_records AS CFR ON CFR.zone_id = CFZ.id 
    WHERE CFZ.name = '$domain' && CFR.type != 'NS' && CFR.isDeleted != 1 && CFZ.isDeleted != 1 ORDER BY type, name");







    if ($getDNSRecordsQuery) {
        if (mysqli_num_rows($getDNSRecordsQuery) < 1) {


            if ($_SESSION['cloudflareAddManually'] == $domain) {

                $DNS_A = dns_get_record($domain, DNS_A);
                $DNS_AAAA = dns_get_record($domain, DNS_AAAA);
                $DNS_MX = dns_get_record($domain, DNS_MX);
                $DNS_TXT = dns_get_record($domain, DNS_TXT);
                $DNS_DMARC = dns_get_record('_dmarc.' . $domain, DNS_TXT);
                $DNS_DKIM = dns_get_record("selector1._domainkey." . $domain, DNS_CNAME);

                $getCloudflareZoneQuery = mysqli_query($conn, "SELECT * FROM $table_cloudflare_zones WHERE name = '$domain' && isDeleted != 1");
                if ($getCloudflareZoneQuery) {
                    if (mysqli_num_rows($getCloudflareZoneQuery) > 0) {
                        $getCloudflareZoneResult = mysqli_fetch_array($getCloudflareZoneQuery);





                        echo '
                        <form onsubmit="displayLoader()" action="" method="post">';


                        echo '
                        <table>
                            <thead>
                                <tr>
                                    <th width="100" align="left" scope="col" >Type</th>
                                    <th align="left" scope="col" >Name</th>
                                    <th align="left" scope="col" >Content</th>
                                    <th width="100" align="center" scope="col" >Proxied</th>
                                    <th width="30" align="right" scope="col" ></th>
                                </tr>
                            </thead>
                        <tbody>';


                        if (!empty($DNS_A)) {
                            foreach ($DNS_A as $DNS) {
                                echo '
                                <tr>
                                    <td align="left" scope="row" data-label="Type">A</td>
                                    <td align="left" scope="row" data-label="Name">' . $DNS['host'] . '</td>
                                    <td align="left" scope="row" data-label="Content">' . $DNS['ip'] . '</td>
                                    <td align="center" scope="row" data-label="Proxied">False</td>
                                    <td align="right" scope="row" data-label="">
                                        <input class="newOverlayRadioInput" type="checkbox" name="ManualCFA[]" value="' . $DNS['host'] . '|' . $DNS['ip'] . '" checked="checked">
                                    </td>
                                </tr>';
                            }
                        }



                        if (!empty($DNS_AAAA)) {
                            foreach ($DNS_AAAA as $DNS) {
                                echo '
                                <tr>
                                    <td align="left" scope="row" data-label="Type">AAAA</td>
                                    <td align="left" scope="row" data-label="Name">' . $DNS['host'] . '</td>
                                    <td align="left" scope="row" data-label="Content">' . $DNS['ipv6'] . '</td>
                                    <td align="center" scope="row" data-label="Proxied">False</td>
                                    <td align="right" scope="row" data-label="">
                                        <input class="newOverlayRadioInput" type="checkbox" name="ManualCFAAAA[]" value="' . $DNS['host'] . '|' . $DNS['ipv6'] . '" checked="checked">
                                    </td>
                                </tr>';
                            }
                        }

                        echo '
                            <tr>
                                <td align="left" scope="row" data-label="Type">CNAME</td>
                                <td align="left" scope="row" data-label="Name">www</td>
                                <td align="left" scope="row" data-label="Content">' . $domain . '</td>
                                <td align="center" scope="row" data-label="Proxied">False</td>
                                <td align="right" scope="row" data-label="">
                                    <input class="newOverlayRadioInput" type="checkbox" name="ManualCFwww" value="' . $domain . '" checked="checked">
                                </td>
                            </tr>';

                        if (!empty($DNS_MX)) {
                            foreach ($DNS_MX as $DNS) {
                                echo '
                                <tr>
                                    <td align="left" scope="row" data-label="Type">MX</td>
                                    <td align="left" scope="row" data-label="Name">' . $DNS['host'] . '</td>
                                    <td align="left" scope="row" data-label="Content">' . $DNS['target'] . '</td>
                                    <td align="center" scope="row" data-label="Proxied">False</td>
                                    <td align="right" scope="row" data-label="">
                                        <input class="newOverlayRadioInput" type="checkbox" name="ManualCFMX[]" value="' . $DNS['host'] . '|' . $DNS['target'] . '|' . $DNS['pri'] . '" checked="checked">
                                    </td>
                                </tr>';
                            }
                        }

                        if (!empty($DNS_TXT)) {
                            foreach ($DNS_TXT as $DNS) {
                                if (strpos($DNS['txt'], "v=spf1") !== false) {
                                    $Type = "SPF";
                                } else {
                                    $Type = "TXT";
                                }
                                echo '
                                <tr>
                                    <td align="left" scope="row" data-label="Type">' . $Type . '</td>
                                    <td align="left" scope="row" data-label="Name">' . $DNS['host'] . '</td>
                                    <td align="left" scope="row" data-label="Content">' . $DNS['txt'] . '</td>
                                    <td align="center" scope="row" data-label="Proxied">False</td>
                                    <td align="right" scope="row" data-label="">
                                        <input class="newOverlayRadioInput" type="checkbox" name="ManualCFTXT[]" value="' . $DNS['host'] . '|' . $DNS['txt'] . '" checked="checked">
                                    </td>
                                </tr>';
                            }
                        }

                        if (!empty($DNS_DMARC)) {
                            foreach ($DNS_DMARC as $DNS) {
                                echo '
                                <tr>
                                    <td align="left" scope="row" data-label="Type">DMARC</td>
                                    <td align="left" scope="row" data-label="Name">' . $DNS['host'] . '</td>
                                    <td align="left" scope="row" data-label="Content">' . $DNS['txt'] . '</td>
                                    <td align="center" scope="row" data-label="Proxied">False</td>
                                    <td align="right" scope="row" data-label="">
                                        <input class="newOverlayRadioInput" type="checkbox" name="ManualCFDMARC[]" value="' . $DNS['host'] . '|' . $DNS['txt'] . '" checked="checked">
                                    </td>
                                </tr>';
                            }
                        }

                        if (!empty($DNS_DKIM)) {
                            foreach ($DNS_DKIM as $DNS) {
                                echo '
                                <tr>
                                    <td align="left" scope="row" data-label="Type">DKIM</td>
                                    <td align="left" scope="row" data-label="Name">' . $DNS['host'] . '</td>
                                    <td align="left" scope="row" data-label="Content">' . $DNS['target'] . '</td>
                                    <td align="center" scope="row" data-label="Proxied">False</td>
                                    <td align="right" scope="row" data-label="">
                                        <input class="newOverlayRadioInput" type="checkbox" name="ManualCFDKIM[]" value="' . $DNS['host'] . '|' . $DNS['target'] . '" checked="checked">
                                    </td>
                                </tr>';
                            }
                        }


                        echo '
                            </tbody>
                        </table></br>';



                        echo '
                        <table>
                            <thead>
                                <tr>
                                    <th width="100" align="left" scope="col" >Type</th>
                                    <th align="left" scope="col" >Name</th>
                                    <th align="left" scope="col" >Content</th>
                                    <th width="100" align="center" scope="col" >Proxied</th>
                                    <th width="30" align="right" scope="col" ></th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td align="left" scope="row" data-label="Type">CNAME</td>
                                    <td align="left" scope="row" data-label="Name">enterpriseregistration</td>
                                    <td align="left" scope="row" data-label="Content">enterpriseregistration.windows.net</td>
                                    <td align="center" scope="row" data-label="Proxied">False</td>
                                    <td align="right" scope="row" data-label="">
                                        <input class="newOverlayRadioInput" type="checkbox" name="ManualCFENTREG" value="true" checked="checked">
                                    </td>
                                </tr>

                                <tr>
                                    <td align="left" scope="row" data-label="Type">CNAME</td>
                                    <td align="left" scope="row" data-label="Name">lyncdiscover</td>
                                    <td align="left" scope="row" data-label="Content">webdir.online.lync.com</td>
                                    <td align="center" scope="row" data-label="Proxied">False</td>
                                    <td align="right" scope="row" data-label="">
                                        <input class="newOverlayRadioInput" type="checkbox" name="ManualCFLYNC" value="true" checked="checked">
                                    </td>
                                </tr>  

                                <tr>
                                    <td align="left" scope="row" data-label="Type">CNAME</td>
                                    <td align="left" scope="row" data-label="Name">autodiscover</td>
                                    <td align="left" scope="row" data-label="Content">autodiscover.outlook.com</td>
                                    <td align="center" scope="row" data-label="Proxied">False</td>
                                    <td align="right" scope="row" data-label="">
                                        <input class="newOverlayRadioInput" type="checkbox" name="ManualCFAUTO" value="true" checked="checked">
                                    </td>
                                </tr>  

                                <tr>
                                    <td align="left" scope="row" data-label="Type">CNAME</td>
                                    <td align="left" scope="row" data-label="Name">sip</td>
                                    <td align="left" scope="row" data-label="Content">sipdir.online.lync.com</td>
                                    <td align="center" scope="row" data-label="Proxied">False</td>
                                    <td align="right" scope="row" data-label="">
                                        <input class="newOverlayRadioInput" type="checkbox" name="ManualCFSIP" value="true" checked="checked">
                                    </td>
                                </tr>  

                                <tr>
                                    <td align="left" scope="row" data-label="Type">SRV</td>
                                    <td align="left" scope="row" data-label="Name">_sip._tls</td>
                                    <td align="left" scope="row" data-label="Content">10 443 sipdir.online.lync.com</td>
                                    <td align="center" scope="row" data-label="Proxied">False</td>
                                    <td align="right" scope="row" data-label="">
                                        <input class="newOverlayRadioInput" type="checkbox" name="ManualCFSIPTLS" value="true" checked="checked">
                                    </td>
                                </tr>

                                <tr>
                                    <td align="left" scope="row" data-label="Type">SRV</td>
                                    <td align="left" scope="row" data-label="Name">_sipfederationtls._tcp</td>
                                    <td align="left" scope="row" data-label="Content">10 5061 sipfed.online.lync.com</td>
                                    <td align="center" scope="row" data-label="Proxied">False</td>
                                    <td align="right" scope="row" data-label="">
                                        <input class="newOverlayRadioInput" type="checkbox" name="ManualCFSIPFED" value="true" checked="checked">
                                    </td>
                                </tr>

                            </tbody>
                        </table></br></br>
                        <input type="hidden" name="ManualCFID" value="' . $getCloudflareZoneResult['id'] . '">
                    
                        <div style="text-align:center;">

                            <div style="display:inline-block;">
                                <form onsubmit="displayLoader()" action="" method="post">
                                    <input name="cloudflareAddManually" type="hidden" value="-1"/>
                                    <span style="font-weight:bold;border:1px solid black;padding:5px;background:#1e5d8b;">
                                        <input class="formtext" style="color:whitesmoke!important;width:150px;" type="submit" value="Cancel">
                                    </span>
                                </form>
                            </div>

                            <div style="display:inline-block;">
                                <span style="font-weight:bold;border:1px solid black;padding:5px;background:#1e5d8b;">
                                    <input class="formtext" style="color:whitesmoke!important;width:150px;" type="submit" name="ManualCFSubmit" value="Add selected">
                                </span>
                            
                            </div>
                        </div>
                    </form>';
                    }
                }
            } else {
                echo '
                    <div style="text-align:center;"></br>
                        <span style="font-weight:bold;">Awaiting records from cloudflare.</span></br></br>
                        <div style="margin:auto;" class="loader"></div></br></br>
                    
                        <form onsubmit="displayLoader()" action="" method="post">
                            <input name="cloudflareAddManually" type="hidden" value="' . $domain . '"/>
                            <span style="font-weight:bold;border:1px solid black;padding:5px;background:#1e5d8b;">
                                <input class="formtext" style="color:whitesmoke!important;width:150px;" type="submit" value="Add manually">
                            </span>
                        </form>
                    </div>';
            }
        } else {



            $checkForDMARCQuery = mysqli_query($conn, "SELECT * 
            FROM $table_cloudflare_zones AS CFZ 
            INNER JOIN $table_cloudflare_records AS CFR ON CFR.zone_id = CFZ.id
            WHERE CFZ.name = '$domain' && CFZ.isDeleted != 1 && CFR.isDeleted != 1 && CFR.type = 'TXT' && CFR.name LIKE '%_dmarc%'");

            if ($checkForDMARCQuery) {
                $dmarcFound = mysqli_num_rows($checkForDMARCQuery);
            }

            $checkForDKIM1Query = mysqli_query($conn, "SELECT * 
            FROM $table_cloudflare_zones AS CFZ 
            INNER JOIN $table_cloudflare_records AS CFR ON CFR.zone_id = CFZ.id
            WHERE CFZ.name = '$domain' && CFZ.isDeleted != 1 && CFR.isDeleted != 1 && CFR.type = 'CNAME' && CFR.name LIKE '%selector1%'");

            if ($checkForDKIM1Query) {
                $dkim1Found = mysqli_num_rows($checkForDKIM1Query);
            }

            $checkForDKIM2Query = mysqli_query($conn, "SELECT * 
            FROM $table_cloudflare_zones AS CFZ 
            INNER JOIN $table_cloudflare_records AS CFR ON CFR.zone_id = CFZ.id
            WHERE CFZ.name = '$domain' && CFZ.isDeleted != 1 && CFR.isDeleted != 1 && CFR.type = 'CNAME' && CFR.name LIKE '%selector2%'");

            if ($checkForDKIM2Query) {
                $dkim2Found = mysqli_num_rows($checkForDKIM2Query);
            }


            $explodedDomain = explode(".", $domain);


            $getTenantDomainQuery = mysqli_query($conn, "SELECT * FROM $table_microsoftdomains WHERE domain LIKE '%$explodedDomain[0]%' && domain LIKE '%onmicrosoft%'");
            if ($getTenantDomainQuery) {
                if (mysqli_num_rows($getTenantDomainQuery) > 0) {
                    $getTenantDomainResult = mysqli_fetch_array($getTenantDomainQuery);

                    $tenantDomain = $getTenantDomainResult['domain'];
                } else {

                    $getTenantIDQuery = mysqli_query($conn, "SELECT * FROM $table_microsoftdomains WHERE domain LIKE '%$explodedDomain[0]%'");
                    if ($getTenantIDQuery) {
                        $getTenantIDResult = mysqli_fetch_array($getTenantIDQuery);

                        $lookupTenantDomainQuery = mysqli_query($conn, "SELECT * 
                        FROM $table_microsoftdomains
                        WHERE tenantID = '$getTenantIDResult[tenantID]' && domain LIKE '%onmicrosoft%'");

                        if ($lookupTenantDomainQuery) {
                            $lookupTenantDomainResult = mysqli_fetch_array($lookupTenantDomainQuery);

                            $tenantDomain = $lookupTenantDomainResult['domain'];
                        }
                    }
                }
            }


            $CFRedirectStatus = 1;
            $getCloudflareZoneDetailsQuery = mysqli_query($conn, "SELECT * FROM $table_cloudflare_zones WHERE name = '$domain' && isDeleted != 1");
            if ($getCloudflareZoneDetailsQuery) {
                if (mysqli_num_rows($getCloudflareZoneDetailsQuery) > 0) {
                    $getCloudflareZoneDetailsResult = mysqli_fetch_array($getCloudflareZoneDetailsQuery);

                    $zoneStatus = $getCloudflareZoneDetailsResult['status'];

                    if (($getCloudflareZoneDetailsResult['status'] == 'pending' || $getCloudflareZoneDetailsResult['status'] == 'disabled') && strpos($domain, '.dk') !== false) {
                        $CFRedirectStatus = 0;
                    }
                }
            }


            $DNS_DKIM = dns_get_record("selector1._domainkey." . $domain, DNS_CNAME);
            foreach ($DNS_DKIM as $DKIM) {
                $DKIM_LOOKUP = dns_get_record($DKIM['target'], DNS_TXT);
            }

            $DKIMActivated = 0;

            if (count($DNS_DKIM) > 0 && count($DKIM_LOOKUP) > 0) {
                $DKIMActivated = 1;
            }

            if ($DKIMActivated == 0) {
                $DNS_DKIM = dns_get_record("selector2._domainkey." . $domain, DNS_CNAME);
                foreach ($DNS_DKIM as $DKIM) {
                    $DKIM_LOOKUP = dns_get_record($DKIM['target'], DNS_TXT);
                }
            }

            if (count($DNS_DKIM) > 0 && count($DKIM_LOOKUP) > 0) {
                $DKIMActivated = 1;
            }





            if ($dmarcFound == 0 || $dkim1Found == 0 || $dkim2Found == 0 || ($DKIMActivated == 0) && $zoneStatus == 'active') {
                echo '
                <table>
                    <thead>
                        <tr>
                            <th align="left" scope="col" >Suggestion</th>
                            <th width="150" align="right" scope="col" ></th>
                        </tr>
                    </thead>
                    <tbody>';

                if ($dmarcFound == 0) {

                    echo '
                    <tr style="vertical-align:top;word-break:break-word;background-color:oldlace;">
                        <td align="left" scope="row" data-label="Suggestion">Add a DMARC record with recommended values</td>
                        <td align="right" scope="row" data-label="">
                            <form onsubmit="displayLoader()" action="" method="post" style="display:inline-block;" title="Add DMARC record">
                                <input name="CFAddDMARCRecordDomain" type="hidden" value="' . $domain . '">
                                <input name="CFAddDMARCRecordName" type="hidden" value="_dmarc.' . $domain . '">
                                <input name="CFAddDMARCRecordContent" type="hidden" value="v=DMARC1; p=reject; sp=reject; adkim=r; aspf=r; fo=1; rf=afrf; rua=mailto:dmarc@cloudpit.dk; ruf=mailto:dmarc@cloudpit.dk; pct=100; ri=86400">
                                <input type="image" src="/png/267-plus.png" width="16" height="16" />
                            </form>
                        </td>
                    </tr>';
                }

                if ($dkim1Found == 0) {

                    $DNS_MX = dns_get_record($domain, DNS_MX);
                    $DNS_MX_TARGET = $DNS_MX[0]['target'];
                    $selectorDomainArray = explode(".", $DNS_MX_TARGET);
                    $selectorDomain = $selectorDomainArray[0];

                    echo '
                    <tr style="vertical-align:top;word-break:break-word;background-color:oldlace;">
                        <td align="left" scope="row" data-label="Suggestion">Add DKIM record with recommended values</td>
                        <td align="right" scope="row" data-label="">
                            <form onsubmit="displayLoader()" action="" method="post" style="display:inline-block;" title="Add DKIM record">
                                <input name="CFAddDKIM1RecordDomain" type="hidden" value="' . $domain . '">
                                <input name="CFAddDKIM1RecordName" type="hidden" value="selector1._domainkey.' . $domain . '">
                                <input name="CFAddDKIM1RecordContent" type="hidden" value="selector1-' . $selectorDomain . '._domainkey.' . $tenantDomain . '">';
                    if ($dkim2Found == 0) {
                        echo '
                                    <input name="CFAddDKIM2RecordDomain" type="hidden" value="' . $domain . '">
                                    <input name="CFAddDKIM2RecordName" type="hidden" value="selector2._domainkey.' . $domain . '">
                                    <input name="CFAddDKIM2RecordContent" type="hidden" value="selector2-' . $selectorDomain . '._domainkey.' . $tenantDomain . '">';
                    }
                    echo '
                                <input type="image" src="/png/267-plus.png" width="16" height="16" />
                            </form>
                        </td>
                    </tr>';
                } elseif ($dkim2Found == 0) {

                    echo '
                    <tr style="vertical-align:top;word-break:break-word;background-color:oldlace;">
                        <td align="left" scope="row" data-label="Suggestion">Add DKIM record with recommended values</td>
                        <td align="right" scope="row" data-label="">
                            <form onsubmit="displayLoader()" action="" method="post" style="display:inline-block;" title="Add DKIM record">
                                <input name="CFAddDKIM2RecordDomain" type="hidden" value="' . $domain . '">
                                <input name="CFAddDKIM2RecordName" type="hidden" value="selector2._domainkey.' . $domain . '">
                                <input name="CFAddDKIM2RecordContent" type="hidden" value="selector2-' . $selectorDomain . '._domainkey.' . $tenantDomain . '">
                                <input type="image" src="/png/267-plus.png" width="16" height="16" />
                            </form>
                        </td>
                    </tr>';
                } elseif ($DKIMActivated == 0 && $zoneStatus == 'active') {
                    echo '
                    <tr style="word-break:break-word;background-color:oldlace;">

                        <td align="left" scope="row" data-label="Suggestion">Enable DKIM</td>
                        <td title="Go to https://security.microsoft.com/dkimv2" align="right" scope="row" data-label=""><a href="https://security.microsoft.com/dkimv2" target="_blank"><img src="/png/383-new-tab.png" width="15" height="15" ></a></td>

                    </tr>';
                }
                echo '    
                    </tbody>
                </table>';
            }












            if ($CFRedirectStatus != 1) {
                echo '
                    <div class="button-flex-container">
                        <span class="divider"><!-- divider --></span>
        
                        <form onsubmit="displayLoader()" action="" method="post" title="Redirect to cloudflare name servers">
                            <input type="hidden" name="redirectDomain2Cloudflare" value="' . $domain . '">
                            <input type="image" style="border-radius: 50%;border:1px solid lightgrey;padding:5px;" src="/png/196-cloud-upload.png" width="24" height="24" />
                        </form>
        
                        <span class="divider"><!-- divider --></span>
                    </div>';
            } elseif ($dmarcFound == 0 || $dkim1Found == 0 || $dkim2Found == 0 || ($DKIMActivated == 0) && $zoneStatus == 'active') {
                echo '
                </br>
                <div class="button-flex-container">
                    <span class="divider"><!-- divider --></span>
                </div>
                </br>';
            }



            echo '
            <table>
                <thead>
                    <tr>
                        <th width="100" align="left" scope="col" >Type</th>
                        <th align="left" scope="col" >Name</th>
                        <th align="left" scope="col" >Content</th>
                        <th width="80" align="right" scope="col" >Proxied</th>
                    </tr>
                </thead>
                <tbody>';

            while ($getDNSRecordsResult = mysqli_fetch_array($getDNSRecordsQuery)) {

                if ($getDNSRecordsResult['type'] == 'A' || $getDNSRecordsResult['type'] == 'AAAA') {

                    $getIPDetailsQuery = mysqli_query($conn, "SELECT * FROM `system-ipstack` WHERE ip = '$getDNSRecordsResult[content]'");
                    if ($getIPDetailsQuery) {
                        if (mysqli_num_rows($getIPDetailsQuery) > 0) {
                            $getIPDetailsResult = mysqli_fetch_array($getIPDetailsQuery);
                            $content = $getDNSRecordsResult['content'] . '
                            <form onsubmit="displayLoader()" action="" method="post" style="display:inline-block;" title="View IP details">
                                <input name="viewIPDetails" type="hidden" value="' . $getIPDetailsResult['id'] . '">
                                <input type="image" src="/png/269-info.png" width="12" height="12" />
                            </form>';
                        } else {
                            $content = $getDNSRecordsResult['content'];
                        }
                    }

                    if (empty($content)) {
                        $content = $getDNSRecordsResult['content'];
                    }
                } else {

                    $content = $getDNSRecordsResult['content'];
                }



                if ($getDNSRecordsResult['proxied'] == 1) {
                    $proxied = '
                    <div>
                        <form onsubmit="displayLoader()" action="" method="post">
                            <label class="switch2">
                                <input type="checkbox" name="" checked onchange="this.form.submit()">
                                <span class="slider2 round2"></span>
                                <input type="hidden" name="disableProxOnCFRecord" value="' . $getDNSRecordsResult['id'] . '">
                                <input type="hidden" name="disableProxOnCFRecordZoneID" value="' . $getDNSRecordsResult['zone_id'] . '">
                            </label>
                        </form>
                    </div>';
                } else {
                    $proxied = '
                    <div>
                        <form onsubmit="displayLoader()" action="" method="post">
                            <label class="switch2">
                                <input type="checkbox" name="" onchange="this.form.submit()">
                                <span class="slider2 round2"></span>
                                <input type="hidden" name="enableProxOnCFRecord" value="' . $getDNSRecordsResult['id'] . '">
                                <input type="hidden" name="enableProxOnCFRecordZoneID" value="' . $getDNSRecordsResult['zone_id'] . '">
                            </label>
                        </form>
                    </div>';
                }

                if (empty($getDNSRecordsResult['proxiable'])) {
                    $proxied = '<div style="width:75px;height:16px;"> </div>';
                }


                if ($getDNSRecordsResult['type'] == 'CNAME' && $getDNSRecordsResult['proxied'] == '') {
                    $checkForProxiedARecordQuery = mysqli_query($conn, "SELECT * 
                    FROM $table_cloudflare_records 
                    WHERE zone_id = '$getDNSRecordsResult[zone_id]' && name = '$getDNSRecordsResult[content]' && (type = 'A' || type = 'AAAA') && proxied = '1' && isDeleted != 1");

                    if ($checkForProxiedARecordQuery) {
                        if (mysqli_num_rows($checkForProxiedARecordQuery) > 0) {
                            $content .= ' 
                            <form onsubmit="displayLoader()" action="" method="post" style="display:inline-block;" title="This record exposes the IP behind ' . $domain . ' which you have proxied through Cloudflare. To fix this, change its proxy status.">
                                <input name="editDNSRecordID" type="hidden" value="' . $getDNSRecordsResult['id'] . '">
                                <input type="image" src="/png/264-warning.png" width="12" height="12" />
                            </form>';
                        }
                    }
                }


                if ($getDNSRecordsResult['type'] == 'TXT' && $getDNSRecordsResult['name'] == "_dmarc.$domain") {

                    $contentArray = explode(" ", $getDNSRecordsResult['content']);
                    foreach ($contentArray as $item) {
                        if (strpos($item, "p=") !== false) {
                            if (substr($item, -1) != ';') {
                                $content .= ' 
                                <form onsubmit="displayLoader()" action="" method="post" style="display:inline-block;" title="The record content has to contain a valid policy tag: p=none;, p=reject;, p=quarantine;.">
                                    <input name="editDNSRecordID" type="hidden" value="' . $getDNSRecordsResult['id'] . '">
                                    <input type="image" src="/png/264-warning.png" width="12" height="12" />
                                </form>';
                            }
                        }
                    }
                }

                echo '
                <tr style="vertical-align:top;word-break: break-word;">
                    <td align="left" scope="row" data-label="Type">' . $getDNSRecordsResult['type'] . '</td>
                    <td align="left" scope="row" data-label="Name">
                        <form onsubmit="displayLoader()" action="" method="post">
                            <input name="editDNSRecordID" type="hidden" value="' . $getDNSRecordsResult['id'] . '">
                            <input class="formtext" type="submit" value="' . $getDNSRecordsResult['name'] . '" title="Edit record">
                        </form>
                    </td>
                    <td align="left" scope="row" data-label="Content">' . $content . '</td>
                    <td align="right" scope="row" data-label="Proxied">' . $proxied . '</td>
                </tr>';
            }


            echo '    
                </tbody>
            </table>';


            echo '</br>
            <table>
                <thead>
                    <tr>
                        <th width="100" align="left" scope="col" >Type</th>
                        <th align="left" scope="col" >Name</th>
                        <th align="left" scope="col" >Status</th>
                        <th width="80" align="right" scope="col" >Enabled</th>
                    </tr>
                </thead>
                <tbody>';



            $getDNSSECQuery = mysqli_query($conn, "SELECT CFD.*
            FROM $table_cloudflare_zones AS CFZ
            INNER JOIN $table_cloudflare_dnssec AS CFD ON CFD.zoneID = CFZ.id
            WHERE CFZ.name = '$domain'");
            if ($getDNSSECQuery) {
                if (mysqli_num_rows($getDNSSECQuery) > 0) {
                    $getDNSSECResult = mysqli_fetch_array($getDNSSECQuery);

                    if ($getDNSSECResult['status'] == 'active') {
                        $DNSSEC = '
                        <div>
                            <form onsubmit="displayLoader()" action="" method="post" title="Disable DNSSEC">
                                <label class="switch2">
                                    <input type="checkbox" name="" checked onchange="this.form.submit()">
                                    <span class="slider2 round2"></span>
                                    <input type="hidden" name="disableDNSSECOnCFZone" value="' . $getDNSSECResult['zoneID'] . '">
                                </label>
                            </form>
                        </div>';
                    } elseif ($getDNSSECResult['status'] == 'disabled') {
                        $DNSSEC = '
                        <div>
                            <form onsubmit="displayLoader()" action="" method="post" title="Enable DNSSEC">
                                <label class="switch2">
                                    <input type="checkbox" name="" onchange="this.form.submit()">
                                    <span class="slider2 round2"></span>
                                    <input type="hidden" name="enableDNSSECOnCFZone" value="' . $getDNSSECResult['zoneID'] . '">
                                </label>
                            </form>
                        </div>';
                    } else {
                        $DNSSEC = '<div title="DNSSEC is pending while we wait for the DS to be added to your registrar. This usually takes ten minutes, but can take up to an hour." 
                        style="width:12px;height:12px;border:6px solid #AAAA;border-top: 6px solid #777;margin-right:9px;display:inline-block;" class="loader">';
                    }



                    echo '
                    <tr>
                        <td align="left" scope="row" data-label="Type">DNSSEC</td>
                        <td align="left" scope="row" data-label="Name">' . $domain . '</td>
                        <td align="left" scope="row" data-label="Status">' . ucfirst($getDNSSECResult['status']) . '</td>
                        <td align="right" scope="row" data-label="Enabled">' . $DNSSEC . '</td>
                    </tr>';
                }
            }

            $getDNSZoneSettingsQuery = mysqli_query($conn, "SELECT CFZS.* 
            FROM $table_cloudflare_zones AS CFZ
            INNER JOIN $table_cloudflare_zones_settings AS CFZS ON CFZS.zone_id = CFZ.id
            WHERE CFZ.name = '$domain' && CFZS.id = 'security_level'");
            if ($getDNSZoneSettingsQuery) {
                if (mysqli_num_rows($getDNSZoneSettingsQuery) > 0) {


                    $getDNSZoneSettingsResult = mysqli_fetch_array($getDNSZoneSettingsQuery);


                    if ($getDNSZoneSettingsResult['value'] == 'under_attack') {
                        $DDOS = '
                    <form onsubmit="displayLoader()" action="" method="post" title="Disables under attack mode">
                        <label class="switch2">
                            <input type="checkbox" name="" checked onchange="this.form.submit()">
                            <span class="slider2 round2"></span>
                            <input type="hidden" name="changeCFDDOS" value="' . $getDNSZoneSettingsResult['zone_id'] . '">
                        </label>
                    </form>';
                    } else {
                        $DDOS = '
                    <form onsubmit="displayLoader()" action="" method="post" title="Enable to switch to under attack mode">
                        <label class="switch2">
                            <input type="checkbox" name="" onchange="this.form.submit()">
                            <span class="slider2 round2"></span>
                            <input type="hidden" name="enableCFDDOS" value="' . $getDNSZoneSettingsResult['zone_id'] . '">
                        </label>
                    </form>';
                    }



                    $DDOSStatus = str_replace("_", " ", $getDNSZoneSettingsResult['value']);

                    echo '
                    <tr>
                        <td align="left" scope="row" data-label="Type">DDOS</td>
                        <td align="left" scope="row" data-label="Name">
                            <form onsubmit="displayLoader()" action="" method="post">
                                <input name="changeCFDDOS" type="hidden" value="' . $getDNSSECResult['zoneID'] . '">
                                <input class="formtext" type="submit" value="' . $domain . '" title="Change security level">
                        </form>
                        </td>
                        <td align="left" scope="row" data-label="Status">' . ucfirst($DDOSStatus) . '</td>
                        <td align="right" scope="row" data-label="Enabled">' . $DDOS . '</td>
                    </tr>';
                }
            }



            $getDevModeSettingsQuery = mysqli_query($conn, "SELECT CFDS.*
            FROM $table_cloudflare_zones AS CFZ
            INNER JOIN $table_cloudflare_devsettings AS CFDS ON CFDS.zoneID = CFZ.id
            WHERE CFZ.name = '$domain'");

            if ($getDevModeSettingsQuery) {
                if (mysqli_num_rows($getDevModeSettingsQuery) > 0) {
                    $getDevModeSettingsResult = mysqli_fetch_array($getDevModeSettingsQuery);

                    if ($getDevModeSettingsResult['value'] == 'on') {
                        $devMode = '
                        <form onsubmit="displayLoader()" action="" method="post" title="">
                            <label class="switch2">
                                <input type="checkbox" name="" checked onchange="this.form.submit()">
                                <span class="slider2 round2"></span>
                                <input type="hidden" name="CFToggleDevModeZoneID" value="' . $getDevModeSettingsResult['zoneID'] . '">
                                <input type="hidden" name="CFToggleDevModeState" value="off">
                            </label>
                        </form>';
                    } else {
                        $devMode = '
                        <form onsubmit="displayLoader()" action="" method="post" title="">
                            <label class="switch2">
                                <input type="checkbox" name="" onchange="this.form.submit()">
                                <span class="slider2 round2"></span>
                                <input type="hidden" name="CFToggleDevModeZoneID" value="' . $getDevModeSettingsResult['zoneID'] . '">
                                <input type="hidden" name="CFToggleDevModeState" value="on">
                            </label>
                        </form>';
                    }



                    echo '
                    <tr>
                        <td align="left" scope="row" data-label="Type">Development</td>
                        <td align="left" scope="row" data-label="Name">' . $domain . '</td>
                        <td align="left" scope="row" data-label="Status">' . ucfirst($getDevModeSettingsResult['value']) . '</td>
                        <td align="right" scope="row" data-label="Enabled">' . $devMode . '</td>
                    </tr>';
                }
            }



            echo '    
                </tbody>
            </table>';




            echo '
            <div class="button-flex-container">
                <span class="divider"><!-- divider --></span>

                <form onsubmit="displayLoader()" action="" method="post" title="Add new record">
                    <input type="hidden" name="addNewCloudflareRecord" value="1">
                    <input type="image" style="border-radius: 50%;border:1px solid lightgrey;padding:5px;" src="/png/267-plus.png" width="24" height="24" />
                </form>

                <span class="divider"><!-- divider --></span>
            </div>';






            $getCloudflareZoneDetailsQuery = mysqli_query($conn, "SELECT * FROM $table_cloudflare_zones WHERE name = '$domain' && isDeleted != 1");
            if ($getCloudflareZoneDetailsQuery) {
                if (mysqli_num_rows($getCloudflareZoneDetailsQuery) > 0) {
                    $getCloudflareZoneDetailsResult = mysqli_fetch_array($getCloudflareZoneDetailsQuery);

                    if ($getCloudflareZoneDetailsResult['status'] == 'pending') {
                        $status = '
                        <div title="Currently pending" 
                        style="width:12px;height:12px;border:6px solid #AAAA;border-top: 6px solid #777;margin-right:9px;display:inline-block;" class="loader">';
                    } else {
                        $status = $getCloudflareZoneDetailsResult['status'];
                    }

                    echo '
                    <table>
                        <thead>
                            <tr>
                                <th align="left" scope="col" >Name server 1</th>
                                <th align="left" scope="col" >Name server 2</th>
                                <th align="right" width="80" scope="col" >Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td align="left" scope="row" data-label="Name server 1">' . $getCloudflareZoneDetailsResult['name_server_1'] . '</td>
                                <td align="left" scope="row" data-label="Name server 2">' . $getCloudflareZoneDetailsResult['name_server_2'] . '</td>
                                <td align="right" scope="row" data-label="Status">' . $status . '</td>
                            </tr>    
                        </tbody>
                    </table>';
                }
            }
        }
    }
} elseif ($_SESSION['NSDomainDetailsView'] == 'ADD2CLOUDFLARE') {

    echo '
<div class="button-flex-container">
<span class="divider"><!-- divider --></span>

<form onsubmit="displayLoader()" action="" method="post" title="Add to cloudflare">
    <input type="hidden" name="AddDomain2Cloudflare" value="' . $domain . '">
    <input type="image" style="border-radius: 50%;border:1px solid lightgrey;padding:5px;" src="/png/add-cloudflare.png" width="24" height="24" />
</form>

<span class="divider"><!-- divider --></span>
</div>';
}



if ($_SESSION['redirectDomain2Cloudflare'] != -1 && !is_null($_SESSION['redirectDomain2Cloudflare'])) {

    echo '
        <div class="newOverlay">';
    if ($_SESSION['redirectDomain2CloudflareError'] != 1) {
        echo '<div class="newOverlayWrapper">';
    } else {
        echo '<div style="background-color:rgb(138, 30, 30, 0.95)!important;" class="newOverlayWrapper">';
    }
    echo '
    
            <div class="newOverlayCloseBtn">
                <form onsubmit="displayLoader()" name="closeOverlay" method="post">
                    <label class="closeOverlay">
                    <img src="/png/close-overlay.png" width="20" height="20" class="pointer" />
                    <input type="hidden" name="redirectDomain2Cloudflare" value="-1">
                    <input type="submit" name="submit">
                    </label>
                </form>
            </div>
    


            <div class="newOverlayContent">
                <form onsubmit="displayLoader()" name="" method="post" style="height:100%;">

                <label for="text">Redirect to cloudflare name servers</label>';
    if ($_SESSION['redirectDomain2CloudflareError'] != 1) {
        echo '<label for="text"></label>';
    } else {
        echo '<label for="text">Something went wrong please try again</label>';
    }
    echo '
                <div class="newOverlayElement Child1">
                <label for="text">This sends out a verification to the customer & starts the cloudflare activation process</label></br></br></br></br></br>

                <div class="newOverlayRadioElementWrapper">
                    <input class="newOverlayRadioInput" type="checkbox" name="redirectDomain2CloudflareConfirm" value="true" required>
                    <label class"newOverlayRadioLabel">Confirm</label>
                </div>

                </div>

                <input type="submit" name="submit" value="Redirect" class="newOverlaySubmit">
            </form>
        </div>

        </div>
    </div>
    ';
}




if ($_SESSION['editDNSRecordID'] != -1 && !is_null($_SESSION['editDNSRecordID'])) {
    $getRecordDetailsQuery = mysqli_query($conn, "SELECT * FROM $table_cloudflare_records WHERE id = '$_SESSION[editDNSRecordID]' && isDeleted != 1");
    if ($getRecordDetailsQuery) {
        $getRecordDetailsResult = mysqli_fetch_array($getRecordDetailsQuery);
        echo '
        <div class="newOverlay">';
        if ($_SESSION['editDNSRecordError'] != 1) {
            echo '<div class="newOverlayWrapper">';
        } else {
            echo '<div style="background-color:rgb(138, 30, 30, 0.95)!important;" class="newOverlayWrapper">';
        }
        echo '
    
            <div class="newOverlayCloseBtn">
                <form onsubmit="displayLoader()" name="closeOverlay" method="post">
                    <label class="closeOverlay">
                    <img src="/png/close-overlay.png" width="20" height="20" class="pointer" />
                    <input type="hidden" name="editDNSRecordID" value="-1">
                    <input type="submit" name="submit">
                    </label>
                </form>
            </div>
    


            <div class="newOverlayContent">
                <form onsubmit="displayLoader()" name="" method="post" style="height:100%;">

                <label for="text">Edit \'' . $getRecordDetailsResult['type'] . '\' record</label>';
        if ($_SESSION['editDNSRecordError'] != 1) {
            echo '<label for="text"></label>';
        } else {
            echo '<label for="text">Something went wrong please try again</label>';
        }
        echo '
                <div class="newOverlayElement Child1">';



        if ($getRecordDetailsResult['type'] == 'SRV') {
            $contentArray = explode("\t", $getRecordDetailsResult['content']);

            $nameArray = explode(".", $getRecordDetailsResult['name']);
            $explodedDomain = explode(".", $domain);

            if ($nameArray[2] == $explodedDomain[0]) {
                $name = "@";
            } else {
                $name = $nameArray[2];
            }

            echo '

            <label for="text">Name:</label>
            <input type="text" name="editDNSRecordName" value="' . $name . '">

            <label for="text">Service:</label>
            <input type="text" name="editDNSRecordService" value="' . $nameArray[0] . '">

            <label for="text">Protocol:</label>
            <input type="text" name="editDNSRecordProto" value="' . $nameArray[1] . '">

            <label for="text">Weight:</label>
            <input type="number" name="editDNSRecordWeight" value="' . $contentArray[0] . '">

            <label for="text">Port:</label>
            <input type="number" name="editDNSRecordPort" value="' . $contentArray[1] . '">

            <label for="text">Target:</label>
            <input type="text" name="editDNSRecordContent" value="' . $contentArray[2] . '">
            ';
        }
        //------> IMPLEMENTATION TO EDIT THE TXT TYPE ON DMARC ---------->//

        elseif ($getRecordDetailsResult['type'] == 'TXT') {

            //--> here implement the Edit Drop-Downs to the content field -->

            $contentArray = explode("; ", $getRecordDetailsResult['content']);

            $nameArray = explode(".", $getRecordDetailsResult['name']);
            $explodedDomain = explode(".", $domain);

            if ($nameArray[0] == $explodedDomain[0]) {
                $name = "@";
            } else {
                $name = $nameArray[0];
            }

            $vValue = $contentArray[0];
            $pValue = $contentArray[1];
            $spValue = $contentArray[3];
            $adkimValue = $contentArray[4];
            $aspfValue = $contentArray[5];
            $foValue = $contentArray[6];
            $rfValue = $contentArray[7];
            $pctValue = $contentArray[9];

            $numbersPct = '';

            for ($i = 0; $i <= 100; $i++) {
                $string_mod = '';

                if ($pctValue == "pct=$i") {

                    $string_mod = 'selected';
                }
                $numbersPct .=  '<option name="" value="' . $i . '" ' . $string_mod . '>' . $i . '</option>';
            }

            //echo $pValue;

            //If STATEMENT TO PRINT THE FORM DEPENT OF WICH KIND OF TXT VALUE IS --> 



           /* elseif ($pValue == 'p=') {
                echo '<option name="" value=""></option>';
              }
          */
         //echo $pValue;

            if ($vValue == 'v=DMARC1') {
                echo '

                <label for="text">Name:</label>
                <input type="text" name="editDNSRecordName" value="' . $name . '">
                <label style="display:block;" name="addNewCloudflareRecordvLabelsecond" for="text"><br> v: </label><br>
                <br><div class="DropDownMenu">
                <select for="text" name="editDNSRecordContentv" style="display:block;">
                <option name="" value="DMARC1" selected >DMARC1</option>
                </select>
                </div>  
    
                <label style="display:block;" name="addNewCloudflareRecordpLabel" for="text">p:</label>
                    <div class="DropDownMenu">
                      <select for="text" name="editDNSRecordContentp"  style="display:block;">
                        <option name="" value="none"';
                if ($pValue == 'p=none') {
                    echo ' selected';} 
                 
                echo '>none</option>
                        <option name="" value="quarantine"';


                if ($pValue == 'p=quarantine') {
                    echo ' selected';
                }
                echo '>quarantine</option>
                        <option name="" value="reject"';

                        
                if ($pValue == 'p=reject') {
                    echo ' selected';
                }
                echo '>reject</option>
                      </select></div>
                    
    
    
                    
                    <label style="display:block;" name="addNewCloudflareRecordspLabel" for="text">sp:</label>
                    <div class="DropDownMenu">
                        <select for="text" name="editDNSRecordContentsp"  style="display:block;">
                        
                        <option name="" value="none"';
                if ($spValue == 'p=none') {
                    echo ' selected';
                }
                echo ' >none</option>
                        <option name="" value="quarantine" ';
                if ($spValue == 'p=quarantine') {
                    echo ' selected';
                }
                echo '>quarantine</option>
                        <option name="" value="reject" ';
                if ($spValue == 'p=reject') {
                    echo ' selected';
                }
                echo '>reject</option>
                        </select>
                     </div>
    
                    <label style="display:block;" name="addNewCloudflareRecordadkimLabel" for="text">adkim:</label>
                    <div class="DropDownMenu">
                        <select for="text" name="editDNSRecordContentadkim"  style="display:block;">
                            
                            <option name="" value="r" ';
                if ($adkimValue == 'p=r') {
                    echo ' selected';
                }
                echo '>r</option>
                            <option name="" value="s" ';
                if ($adkimValue == 'p=s') {
                    echo ' selected';
                }
                echo '>s</option>
                        </select>
                    </div>
    
                    <label style="display:block;" name="addNewCloudflareRecordaspfLabel" for="text">aspf:</label>
                    <div class="DropDownMenu">
                        <select for="text" name="editDNSRecordContentaspf"  style="display:block;">
                            
                            <option name="" value="r" ';
                if ($aspfValue == 'p=r') {
                    echo ' selected';
                }
                echo '>r</option>
                            <option name="" value="s" ';
                if ($aspfValue == 'p=s') {
                    echo ' selected';
                }
                echo '>s</option>
                        </select>
                    </div>
    
                    <label style="display:block;" name="addNewCloudflareRecordfoLabel" for="text">fo:</label>
                    <div class="DropDownMenu">
                        <select for="text" name="editDNSRecordContentfo"  style="display:block;">
                           
                            <option name="" value="0" ';
                if ($foValue == 'p=0') {
                    echo ' selected';
                }
                echo '>0</option>
                            <option name="" value="1" ';
                if ($foValue == 'p=1') {
                    echo ' selected';
                }
                echo '>1</option>
                            <option name="" value="d" ';
                if ($foValue == 'p=d') {
                    echo ' selected';
                }
                echo '>d</option>
                            <option name="" value="s" ';
                if ($foValue == 'p=s') {
                    echo ' selected';
                }
                echo '>s</option>
                        </select>
                    </div>
    
                    <label style="display:block;" name="addNewCloudflareRecordrfLabel" for="text">rf:</label>
                    <div class="DropDownMenu">
                        <select for="text" name="editDNSRecordContentrf"  style="display:block;">
                            
                            <option name="" value="afrf" ';
                if ($rfValue == 'p=afrf') {
                    echo ' selected';
                }
                echo '>afrf</option>
    
    
                <option name="" value="iodef" ';
                if ($rfValue == 'p=iodef') {
                    echo ' selected';
                }
                echo '>iodef</option>
    
    
                        </select>
                    </div>
    
                    <label style="display:block;" name="addNewCloudflareRecordruaLabel" for="text">rua:</label>
                    <div class="DropDownMenu">
                        <select for="text" name="editDNSRecordContentrua" style="display:block;">
                            <option name="" value="mailto:dmarc@cloudpit.dk" selected>mailto:dmarc@cloudpit.dk</option>
                        </select>
                    </div>
    
                    <label style="display:block;" name="addNewCloudflareRecordrufLabel" for="text">ruf:</label>
                    <div class="DropDownMenu">
                        <select for="text" name="editDNSRecordContentruf"  style="display:block;">
                        <option name="" value="mailto:dmarc@cloudpit.dk" selected>mailto:dmarc@cloudpit.dk</option>
                        </select>
                    </div>
    
                    <label style="display:block;" name="addNewCloudflareRecordpctLabel" for="text">pct:</label>
                    <div class="DropDownMenu">
                    <select for="text" name="editDNSRecordContentpct" style="display:block;">
                    ' . $numbersPct . ' 
                    </select>
                    </div>
    
                    <label style="display:block;" name="addNewCloudflareRecordriLabel" for="text">ri:</label>
                    <div class="DropDownMenu">
                        <select for="text" name="editDNSRecordContentri"  style="display:block;">
                            <option name="" value="86400" selected>86400 ( daily )</option>
                        </select>
                    </div>
    
    
                <label for="text">TTL:</label>
                <input type="text" name="editDNSRecordTTL" value="' . $getRecordDetailsResult['ttl'] . '">
    
        
                ';
            } else {


                echo '

                <label for="text">Name:</label>
                <input type="text" name="editDNSRecordName" value="' . $name . '">

                <label for="text">Content:</label>
                <input type="text" name="editDNSRecordContent" value="' . $getRecordDetailsResult['content'] . '">
               
                <label for="text">TTL:</label>
                <input type="text" name="editDNSRecordTTL" value="' . $getRecordDetailsResult['ttl'] . '">
    
        
                ';
            }
        } else {
            $nameArray = explode(".", $getRecordDetailsResult['name']);
            $explodedDomain = explode(".", $domain);

            if ($nameArray[0] == $explodedDomain[0]) {
                $name = "@";
            } else {
                $name = $nameArray[0];
            }

            echo '
            <label for="text">Name:</label>
            <input type="text" name="editDNSRecordName" value="' . $name . '">

            
             
            
            <label for="text">Content:</label>
            <input type="text" name="editDNSRecordContent" value="' . $getRecordDetailsResult['content'] . '">

           
            <label for="text">TTL:</label>
            <input type="text" name="editDNSRecordTTL" value="' . $getRecordDetailsResult['ttl'] . '">
            ';
        }

        echo '
            <label for="text">Priority:</label>
            <input type="number" name="editDNSRecordpriority" value="' . $getRecordDetailsResult['priority'] . '">
        ';

        if ($getRecordDetailsResult['proxiable'] == '1') {
            echo '
            <label for="text">Proxied:</label>
            <div style="width:90%;margin:auto;">
                <label class="switch">
                    <input type="checkbox" name="editDNSRecordProxied"';
            if ($getRecordDetailsResult['proxied'] == '1') {
                echo 'checked';
            }
            echo '>
                    <span class="slider round"></span>
                </label>
            
            </div>';
        }

        echo '
              
                </div>
                <input type="hidden" name="editDNSRecordType" value="' . $getRecordDetailsResult['type'] . '">
                <input type="hidden" name="editDNSRecordZoneID" value="' . $getRecordDetailsResult['zone_id'] . '">
                <div style="margin:auto;width:90%;text-align:center;">
                    <input type="submit" name="editDNSRecordDelete" value="Delete" class="newOverlaySubmit" onclick="return  confirm(`Are you sure to delete?`)" style="width:40%;display:inline-block;margin-right:15px;background-color:#8A1E1E;color:whitesmoke!important;border-color:#8A1E1E;">
                    <input type="submit" name="editDNSRecordSave" value="Save" class="newOverlaySubmit" style="width:40%;display:inline-block;" title="Add to cloudflare">
                </div>';


        if ($getRecordDetailsResult['type'] == 'TXT' && $name == '_dmarc') {
            echo '
                <div class="newOverlayDescriptionField">
                    <div class="newOverlayDescription">';
            $contentArray = explode(" ", $getRecordDetailsResult['content']);
            foreach ($contentArray as $item) {
                if (strpos($item, "p=") !== false) {
                    if (substr($item, -1) != ';') {
                        echo '
                            <img src="/png/264-warning.png" width="24" height="24">
                            <span style="vertical-align:super;margin-left:2px;">The record content has to contain a valid policy tag.</br></br></span>';
                    }
                }
            }

            if ($getRecordDetailsResult['content'] != 'v=DMARC1; p=reject; sp=reject; adkim=r; aspf=r; fo=1; rf=afrf; rua=mailto:dmarc@cloudpit.dk; ruf=mailto:dmarc@cloudpit.dk; pct=100; ri=86400') {


                echo '
                    <img src="/png/269-info.png" width="24" height="24">
                    <span class="noSelect" style="vertical-align:super;margin-left:2px;cursor:pointer;" onclick="applyDMARC()">Click here to apply the recommended values</span>';
            }
            echo '
                </div></br>
            </div>';
        }



        echo '
            </form>
        </div>

        </div>
    </div>
    ';
    }
}






if ($_SESSION['viewIPDetails'] != -1 && !is_null($_SESSION['viewIPDetails'])) {

    $getIPDetailsQuery = mysqli_query($conn, "SELECT * FROM `system-ipstack` WHERE id = $_SESSION[viewIPDetails]");
    if ($getIPDetailsQuery) {
        if (mysqli_num_rows($getIPDetailsQuery) > 0) {
            $getIPDetailsResult = mysqli_fetch_array($getIPDetailsQuery);
            echo '
            <div class="newOverlay">';
            if ($_SESSION['viewIPDetailsError'] != 1) {
                echo '<div class="newOverlayWrapper">';
            } else {
                echo '<div style="background-color:rgb(138, 30, 30, 0.95)!important;" class="newOverlayWrapper">';
            }
            echo '
        
                <div class="newOverlayCloseBtn">
                    <form onsubmit="displayLoader()" name="closeOverlay" method="post">
                        <label class="closeOverlay">
                        <img src="/png/close-overlay.png" width="20" height="20" class="pointer" />
                        <input type="hidden" name="viewIPDetails" value="-1">
                        <input type="submit" name="submit">
                        </label>
                    </form>
                </div>
        
    
    
                <div class="newOverlayContent">
                    <form onsubmit="displayLoader()" name="" method="post" style="height:100%;">
    
                    <label for="text">IP Address details</label>';
            if ($_SESSION['viewIPDetailsError'] != 1) {
                echo '<label for="text"></label>';
            } else {
                echo '<label for="text">Something went wrong please try again</label>';
            }
            $date = new DateTime("now", new DateTimeZone($getIPDetailsResult['time_zone_id']));
            $ISPName = str_replace("a S", "A/S", $getIPDetailsResult['connection_isp']);

            if ($getIPDetailsResult['zip'] == '-1') {
                $getIPDetailsResult['zip'] = "";
            }

            if ($getIPDetailsResult['reverse_ip'] == '-1') {
                $reverseIP = "N/A";
            } else {
                $reverseIP = $getIPDetailsResult['reverse_ip'];
            }


            echo '
                    <div class="newOverlayElement Child1">

                        <div class="newOverlayRadioElementWrapper">
                            <div style="padding-top:2px;padding-bottom:2px;">
                                <span style="font-weight:bold;">IP Address: </span>
                                <span style="float:right">' . $getIPDetailsResult['ip'] . '</span>
                            </div>

                            <div style="padding-top:2px;padding-bottom:2px;">
                                <span style="font-weight:bold;">Type: </span>
                                <span style="float:right">' . $getIPDetailsResult['type'] . '</span>
                            </div>
                            
                            <div style="padding-top:2px;padding-bottom:2px;">
                                <span style="font-weight:bold;">Continent: </span>
                                <span style="float:right">' . $getIPDetailsResult['continent_name'] . '</span>
                            </div>
                            
                            <div style="padding-top:2px;padding-bottom:2px;">
                                <span style="font-weight:bold;">Country: </span>
                                <span style="float:right">' . $getIPDetailsResult['country_name'] . ' <img src="' . $getIPDetailsResult['country_flag'] . '" height="12"></span>
                            </div>
                            
                            <div style="padding-top:2px;padding-bottom:2px;">
                                <span style="font-weight:bold;">Region: </span>
                                <span style="float:right">' . $getIPDetailsResult['region_name'] . '</span>
                            </div>
                            
                            <div style="padding-top:2px;padding-bottom:2px;">
                                <span style="font-weight:bold;">City: </span>
                                <span style="float:right">' . $getIPDetailsResult['city'] . ' ' . $getIPDetailsResult['zip'] . '</span>
                            </div>
                            
                            <div style="padding-top:2px;padding-bottom:2px;">
                                <span style="font-weight:bold;">Local Time: </span>
                                <span style="float:right">' . $date->format('d-m-Y H:i:s') . ' ' . $getIPDetailsResult['time_zone_code'] . '</span>
                            </div>
                            
                            <div style="padding-top:2px;padding-bottom:2px;">
                                <span style="font-weight:bold;">ISP: </span>
                                <span style="float:right">' . $ISPName . '</span>
                            </div>

                            <div style="padding-top:2px;padding-bottom:2px;">
                                <span style="font-weight:bold;">Reverse ip: </span>
                                <span style="float:right">' . $reverseIP . '</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
    
            </div>
        </div>
        ';
        }
    }
}

//----------------------------------- DMARC Drop Down Descriptions --------------------//

$v = "The DMARC version should always be 'DKIM1'. Note: A wrong, or absent DMARC version tag would cause the entire record to be ignored.";

$p = "Policy applied to emails that fails the DMARC check. Authorized values: 'none', 'quarantine', or 'reject'.
'none' is used to collect feedback and gain visibility into email streams without impacting existing flows.
'quarantine' allows Mail Receivers to treat email that fails the DMARC check as suspicious. Most of the time, they will end up in your SPAM folder.
'reject' outright rejects all emails that fail the DMARC check.";

$sp = "Policy to apply to email from a sub-domain of this DMARC record that fails the DMARC check. 
Authorized values: 'none', 'quarantine', or 'reject'.
This tag allows domain owners to explicitly publish a 'wildcard' sub-domain policy.";

$adkim = "Specifies 'Alignment Mode' for DKIM signatures. 
Authorized values: 'r', 's'.
'r', or 'Relaxed Mode', allows Authenticated DKIM d= domains that share a common Organizational Domain with an email's 'header-From:' domain to pass the DMARC check.
's', or 'Strict Mode' requires exact matching between the DKIM d= domain and an email's 'header-From:' domain.";

$aspf = "Specifies 'Alignment Mode' for SPF. Authorized values: 'r', 's'.
'r', or 'Relaxed Mode' allows SPF Authenticated domains that share a common Organizational Domain with an email's 'header-From:' domain to pass the DMARC check.
's', or 'Strict Mode' requires exact matching between the SPF domain and an email's 'header-From:' domain.";

$fo = "Forensic reporting options. Authorized values: '0', '1', 'd', or 's'.
'0' generates reports if all underlying authentication mechanisms fail to produce a DMARC pass result,
'1' generates reports if any mechanisms fail,
'd' generates reports if DKIM signature failed to verify,
's' generates reports if SPF failed.";

$rf = "The reporting format for individual Forensic reports. Authorized values: 'afrf', 'iodef'.";

$rua = "The list of URIs for receivers to send XML feedback to. Note: This is not a list of email addresses, as DMARC requires a list of URIs of the form 'mailto:address@example.org'.";

$ruf = "The list of URIs for receivers to send Forensic reports to. Note: This is not a list of email addresses, as DMARC requires a list of URIs of the form 'mailto:address@example.org'.";

$pct = "The percentage tag tells receivers to only apply policy against email that fails the DMARC check x amount of the time. For example, 'pct=25' tells receivers to apply the 'p=' policy 25% of the time against email that fails the DMARC check. Note: The policy must be 'quarantine' or 'reject' for the percentage tag to be applied.";

$ri = "The reporting interval for how often you'd like to receive aggregate XML reports. You'll most likely receive reports once a day regardless of this setting. 86400 ( daily )";


if ($_SESSION['addNewCloudflareRecord'] != -1 && !is_null($_SESSION['addNewCloudflareRecord'])) {

    $getCloudflareZoneIDQuery = mysqli_query($conn, "SELECT * FROM $table_cloudflare_zones WHERE name = '$domain' && isDeleted != 1");
    if ($getCloudflareZoneIDQuery) {
        $getCloudflareZoneIDResult = mysqli_fetch_array($getCloudflareZoneIDQuery);

        $zoneID = $getCloudflareZoneIDResult['id'];
    }

    echo '
        <div class="newOverlay">';
    if ($_SESSION['addNewCloudflareRecordError'] != 1) {
        echo '<div class="newOverlayWrapper">';
    } else {
        echo '<div style="background-color:rgb(138, 30, 30, 0.95)!important;" class="newOverlayWrapper">';
    }
    echo '
    
            <div class="newOverlayCloseBtn">
                <form onsubmit="displayLoader()" name="closeOverlay" method="post">
                    <label class="closeOverlay">
                    <img src="/png/close-overlay.png" width="20" height="20" class="pointer" />
                    <input type="hidden" name="addNewCloudflareRecord" value="-1">
                    <input type="submit" name="submit">
                    </label>
                </form>
            </div>
    


            <div class="newOverlayContent">
                <form onsubmit="displayLoader()" name="" method="post" style="height:100%;">

                <label for="text">Add new record to cloudflare</label>';
    if ($_SESSION['addNewCloudflareRecordError'] != 1) {
        echo '<label for="text"></label>';
    } else {
        echo '<label for="text">Something went wrong please try again</label>';
    }

    $numbers = '';
    for ($i = 0; $i <= 99; $i++) {
        $numbers .=  '<option name="" value="' . $i . '">' . $i . '</option>';
    }

    echo '
                <div class="newOverlayElement Child1">

                    <label for="text">Type:</label>
                    <div class="DropDownMenu">
                        <select for="text" name="addNewCloudflareRecordType" required onchange="addNewCloudflareRecordShowFields()">
                            <option name="" value="" disabled selected>Select type</option>
                            <option name="" value="A" >A</option>
                            <option name="" value="AAAA" >AAAA</option>
                            <option name="" value="CNAME" >CNAME</option>
                            <option name="" value="DNSKEY" >DNSKEY</option>
                            <option name="" value="MX" >MX</option>
                            <option name="" value="SRV" >SRV</option>
                            <option name="" value="TXT" >TXT</option>
                            <option name="" value="TXT" >test</option>
                            <option name="" value="DMARC" >DMARC</option>
                        </select>
                    </div>

                    

                <label style="display:none;" name="addNewCloudflareRecordNameLabel" for="text">Name:</label>
                <input style="display:none;" type="text" name="addNewCloudflareRecordName" value="" placeholder="@ for root" required>
    
                <label style="display:none;" name="addNewCloudflareRecordServiceLabel" for="text">Service:</label>
                <input style="display:none;" type="text" name="addNewCloudflareRecordService" value="" placeholder="_servicename">
    
                <label style="display:none;" name="addNewCloudflareRecordProtoLabel" for="text">Protocol:</label>
                <input style="display:none;" type="text" name="addNewCloudflareRecordProto" value="" placeholder="_protocol">
    
                <label style="display:none;" name="addNewCloudflareRecordWeightLabel" for="text">Weight:</label>
                <input style="display:none;" type="number" name="addNewCloudflareRecordWeight" value="">
    
                <label style="display:none;" name="addNewCloudflareRecordPortLabel" for="text">Port:</label>
                <input style="display:none;" type="number" name="addNewCloudflareRecordPort" value="">
    
                <label style="display:none;" name="addNewCloudflareRecordTargetLabel" for="text">Target:</label>
                <input style="display:none;" type="text" name="addNewCloudflareRecordTarget" value="">

                <label style="display:none;" name="addNewCloudflareRecordContentLabel" for="text">Content:</label>
                <input style="display:none;" type="text" name="addNewCloudflareRecordContent" value="">

                <label style="display:none;" name="addNewCloudflareRecordTTLLabel" for="text">TTL:</label>
                <input style="display:none;" type="number" name="addNewCloudflareRecordTTL" value="" placeholder="Min 60, max 2147483647, or 1 for auto">



            
                <label style="display:none;" name="addNewCloudflareRecordvLabel" for="text"> CONTENT </label>
                <label style="display:none;" name="addNewCloudflareRecordvLabelsecond" for="text"> v: </label>
                <div class="DropDownMenu">
                    <select for="text" name="addNewCloudflareRecordv" required style="display:none;">
                        
                        <option name="" value="DMARC1" selected >DMARC1</option>
                     </select>
                    <p style="display:none;" name="addNewCloudflareRecordvLabelv" for="text">' . $v . '</p>
                </div>
                
                

                <label style="display:none;" name="addNewCloudflareRecordpLabel" for="text">p:</label>
                <div class="DropDownMenu">
                    <select for="text" name="addNewCloudflareRecordp" required style="display:none;">
                        <option name="" value="none" >none</option>
                        <option name="" value="quarantine" >quarantine</option>
                        <option name="" value="reject"selected >reject</option>
                    </select>
                    <p style="display:none;" name="addNewCloudflareRecordpLabelp" for="text">' . $p . '</p>
                </div>

                <label style="display:none;" name="addNewCloudflareRecordspLabel" for="text">sp:</label>
                <div class="DropDownMenu">
                    <select for="text" name="addNewCloudflareRecordsp" required style="display:none;">
                    
                    <option name="" value="none" >none</option>
                    <option name="" value="quarantine" >quarantine</option>
                    <option name="" value="reject"selected >reject</option>
                    </select>
                    <p style="display:none;" name="addNewCloudflareRecordspLabelsp" for="text">' . $sp . '</p>
                </div>

                <label style="display:none;" name="addNewCloudflareRecordadkimLabel" for="text">adkim:</label>
                <div class="DropDownMenu">
                    <select for="text" name="addNewCloudflareRecordadkim" required style="display:none;">
                        <option name="" value="r" selected >r</option>
                        <option name="" value="s" >s</option>
                    </select>
                    <p style="display:none;" name="addNewCloudflareRecordadkimLabeladkim" for="text">' . $adkim . '</p>
                </div>

                <label style="display:none;" name="addNewCloudflareRecordaspfLabel" for="text">aspf:</label>
                <div class="DropDownMenu">
                    <select for="text" name="addNewCloudflareRecordaspf" required style="display:none;">
                        
                        <option name="" value="r"selected >r</option>
                        <option name="" value="s" >s</option>
                    </select>
                    <p style="display:none;" name="addNewCloudflareRecordaspfLabelaspf" for="text">' . $aspf . '</p>
                </div>

                <label style="display:none;" name="addNewCloudflareRecordfoLabel" for="text">fo:</label>
                <div class="DropDownMenu">
                    <select for="text" name="addNewCloudflareRecordfo" required style="display:none;">
                       
                        <option name="" value="0" >0</option>
                        <option name="" value="1"selected >1</option>
                        <option name="" value="d" >d</option>
                        <option name="" value="s" >s</option>
                    </select>
                    <p style="display:none;" name="addNewCloudflareRecordfoLabelfo" for="text">' . $fo . '</p>
                </div>

                <label style="display:none;" name="addNewCloudflareRecordrfLabel" for="text">rf:</label>
                <div class="DropDownMenu">
                    <select for="text" name="addNewCloudflareRecordrf" required style="display:none;">
                        
                        <option name="" value="afrf"selected >afrf</option>
                        <option name="" value="iodef" >iodef</option>
                    </select>
                    <p style="display:none;" name="addNewCloudflareRecordrfLabelrf" for="text">' . $rf . '</p>
                </div>

                <label style="display:none;" name="addNewCloudflareRecordruaLabel" for="text">rua:</label>
                <div class="DropDownMenu">
                    <select for="text" name="addNewCloudflareRecordrua" required style="display:none;">
                        
                        <option name="" value="mailto:dmarc@cloudpit.dk"selected>mailto:dmarc@cloudpit.dk</option>
                    </select>
                    <p style="display:none;" name="addNewCloudflareRecordruaLabelrua" for="text">' . $rua . '</p>
                </div>

                <label style="display:none;" name="addNewCloudflareRecordrufLabel" for="text">ruf:</label>
                <div class="DropDownMenu">
                    <select for="text" name="addNewCloudflareRecordruf" required style="display:none;">
                   
                    <option name="" value="mailto:dmarc@cloudpit.dk"selected>mailto:dmarc@cloudpit.dk</option>
                    </select>
                    <p style="display:none;" name="addNewCloudflareRecordrufLabelruf" for="text">' . $ruf . '</p>
                </div>

                <label style="display:none;" name="addNewCloudflareRecordpctLabel" for="text">pct:</label>
                <div class="DropDownMenu">
                    <select for="text" name="addNewCloudflareRecordpct" required style="display:none;">
                        <option name="" value="" disabled >Select a number</option>
                        <option name="" value="numbers" selected>' . $numbers . '</option>
                        <option name="" value="100" selected>100</option>
                    </select>
                    <p style="display:none;" name="addNewCloudflareRecordpctLabelpct" for="text">' . $pct . '</p>
                </div>

                <label style="display:none;" name="addNewCloudflareRecordriLabel" for="text">ri:</label>
                <div class="DropDownMenu">
                    <select for="text" name="addNewCloudflareRecordri" required style="display:none;">
                        
                        <option name="" value="86400" selected>86400 ( daily )</option>
                    </select>
                    <p style="display:none;" name="addNewCloudflareRecordriLabelri" for="text">' . $ri . '</p>
                </div>

               
 
                <label style="display:none;" name="addNewCloudflareRecordPriorityLabel" for="text">Priority:</label>
                <input style="display:none;" type="number" name="addNewCloudflareRecordPriority" value="" required>';



    echo '
                </div>
                <input type="hidden" name="addNewCloudflareRecordZoneID" value="' . $zoneID . '">
                <input style="display:none;" type="submit" name="addNewCloudflareRecordSubmit" value="Add" class="newOverlaySubmit">
            </form>
        </div>

        </div>
    </div>
    ';
}

if ($_SESSION['changeCFDDOS'] != -1 && !is_null($_SESSION['changeCFDDOS'])) {

    $getCurrentSecurityLevelQuery = mysqli_query($conn, "SELECT * FROM $table_cloudflare_zones_settings WHERE zone_id = '$_SESSION[changeCFDDOS]' && id = 'security_level'");
    $getCurrentSecurityLevelResult = mysqli_fetch_array($getCurrentSecurityLevelQuery);

    echo '
        <div class="newOverlay">';
    if ($_SESSION['changeCFDDOSError'] != 1) {
        echo '<div class="newOverlayWrapper">';
    } else {
        echo '<div style="background-color:rgb(138, 30, 30, 0.95)!important;" class="newOverlayWrapper">';
    }
    echo '
    
            <div class="newOverlayCloseBtn">
                <form onsubmit="displayLoader()" name="closeOverlay" method="post">
                    <label class="closeOverlay">
                    <img src="/png/close-overlay.png" width="20" height="20" class="pointer" />
                    <input type="hidden" name="changeCFDDOS" value="-1">
                    <input type="submit" name="submit">
                    </label>
                </form>
            </div>
    


            <div class="newOverlayContent">
                <form onsubmit="displayLoader()" name="" method="post" style="height:100%;">

                <label for="text">Change security level</label>';
    if ($_SESSION['changeCFDDOSError'] != 1) {
        echo '<label for="text"></label>';
    } else {
        echo '<label for="text">Something went wrong please try again</label>';
    }
    echo '
                <div class="newOverlayElement Child1">


                    <label for="text">Security level</label>
                    <div class="DropDownMenu">
                        <select for="text" name="changeCFDDOSValue" required">
                        
                            <option name="" value="essentially_off"';
    if ($getCurrentSecurityLevelResult['value'] == 'essentially_off') {
        echo ' disabled selected';
    }
    echo ' >essentially off</option>

                            <option name="" value="low"';
    if ($getCurrentSecurityLevelResult['value'] == 'low') {
        echo ' disabled selected';
    }
    echo ' >low</option>

                            <option name="" value="medium"';
    if ($getCurrentSecurityLevelResult['value'] == 'medium') {
        echo ' disabled selected';
    }
    echo ' >medium</option>

                            <option name="" value="high"';
    if ($getCurrentSecurityLevelResult['value'] == 'high') {
        echo ' disabled selected';
    }
    echo ' >High</option>

                            <option name="" value="under_attack"';
    if ($getCurrentSecurityLevelResult['value'] == 'under_attack') {
        echo ' disabled selected';
    }
    echo '  >Under attack</option>
                        </select>
                    </div>

                </div>

                <input type="submit" name="submit" value="Change" class="newOverlaySubmit">
            </form>
        </div>

        </div>
    </div>
    ';
}



?>

<script>
    function copy2Clipboard(element) {
        if (element == "DNS_DKIM_SUGGESTION_1_1") {
            navigator.clipboard.writeText("selector1._domainkey");
        } else if (element == "DNS_DKIM_SUGGESTION_2_1") {
            navigator.clipboard.writeText("selector2._domainkey");
        } else {
            let textElement = document.getElementsByClassName(element)[0];
            let text = textElement.innerText;
            text = text.replace(/[\n\r]/g, " ");
            navigator.clipboard.writeText(text);
        }

        let popupElement = document.getElementsByClassName('popupHidden')[0];
        popupElement.classList.remove("popuphideMe");
        popupElement.classList.add("popupshowMe");

        setTimeout(function() {
            popupElement.classList.remove("popupshowMe");
            popupElement.classList.add("popuphideMe");
        }, 1500);
    }


    function applyDMARC() {
        document.getElementsByName('editDNSRecordContent')[0].value = `v=DMARC1; p=reject; sp=reject; adkim=r; aspf=r; fo=1; rf=afrf; rua=mailto:dmarc@cloudpit.dk; ruf=mailto:dmarc@cloudpit.dk; pct=100; ri=86400`;

        //  let selectorValueEdit = document.getElementsByName('editDNSRecordContent')[0].value;

    }


    function addNewCloudflareRecordShowFields() {
        let selectorValue = document.getElementsByName('addNewCloudflareRecordType')[0].value;


        document.getElementsByName('addNewCloudflareRecordNameLabel')[0].style.display = "block";
        document.getElementsByName('addNewCloudflareRecordName')[0].style.display = "block";

        document.getElementsByName('addNewCloudflareRecordServiceLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordService')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordService')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordProtoLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordProto')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordProto')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordWeightLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordWeight')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordWeight')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordPortLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordPort')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordPort')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordTargetLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordTarget')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordTarget')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordContentLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordContent')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordContent')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordTTLLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordTTL')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordTTL')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordvLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordvLabelsecond')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordvLabelv')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordv')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordv')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordpLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordpLabelp')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordp')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordp')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordspLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordspLabelsp')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordsp')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordsp')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordadkimLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordadkimLabeladkim')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordadkim')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordadkim')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordaspfLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordaspfLabelaspf')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordaspf')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordaspf')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordfoLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordfoLabelfo')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordfo')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordfo')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordrfLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordrfLabelrf')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordrf')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordrf')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordruaLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordruaLabelrua')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordrua')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordrua')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordrufLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordrufLabelruf')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordruf')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordruf')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordpctLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordpctLabelpct')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordpct')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordpct')[0].removeAttribute("required");

        document.getElementsByName('addNewCloudflareRecordriLabel')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordriLabelri')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordri')[0].style.display = "none";
        document.getElementsByName('addNewCloudflareRecordri')[0].removeAttribute("required");



        if (selectorValue == 'A' || selectorValue == 'AAAA') {
            document.getElementsByName('addNewCloudflareRecordContent')[0].placeholder = "Ip address";
        } else if (selectorValue == 'CNAME') {
            document.getElementsByName('addNewCloudflareRecordContent')[0].placeholder = "Target";
        } else {
            document.getElementsByName('addNewCloudflareRecordContent')[0].placeholder = "";
        }


        if (selectorValue == 'SRV') {
            document.getElementsByName('addNewCloudflareRecordServiceLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordService')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordService')[0].setAttribute("required", "");

            document.getElementsByName('addNewCloudflareRecordProtoLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordProto')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordProto')[0].setAttribute("required", "");

            document.getElementsByName('addNewCloudflareRecordWeightLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordWeight')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordWeight')[0].setAttribute("required", "");

            document.getElementsByName('addNewCloudflareRecordPortLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordPort')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordPort')[0].setAttribute("required", "");

            document.getElementsByName('addNewCloudflareRecordTargetLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordTarget')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordTarget')[0].setAttribute("required", "");

            document.getElementsByName('addNewCloudflareRecordPriority')[0].placeholder = "Recommended value: 5";

        } else if (selectorValue != 'SRV') {

            document.getElementsByName('addNewCloudflareRecordContentLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordContent')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordContent')[0].setAttribute("required", "");

            document.getElementsByName('addNewCloudflareRecordTTLLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordTTL')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordTTL')[0].setAttribute("required", "");

        }


        document.getElementsByName('addNewCloudflareRecordPriorityLabel')[0].style.display = "block";
        document.getElementsByName('addNewCloudflareRecordPriority')[0].style.display = "block";
        document.getElementsByName('addNewCloudflareRecordSubmit')[0].style.display = "block";




        if (selectorValue == 'DMARC') {
            document.getElementsByName('addNewCloudflareRecordNameLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordName')[0].style.display = "block";

            document.getElementsByName('addNewCloudflareRecordServiceLabel')[0].style.display = "none";
            document.getElementsByName('addNewCloudflareRecordService')[0].style.display = "none";
            document.getElementsByName('addNewCloudflareRecordService')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordProtoLabel')[0].style.display = "none";
            document.getElementsByName('addNewCloudflareRecordProto')[0].style.display = "none";
            document.getElementsByName('addNewCloudflareRecordProto')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordWeightLabel')[0].style.display = "none";
            document.getElementsByName('addNewCloudflareRecordWeight')[0].style.display = "none";
            document.getElementsByName('addNewCloudflareRecordWeight')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordPortLabel')[0].style.display = "none";
            document.getElementsByName('addNewCloudflareRecordPort')[0].style.display = "none";
            document.getElementsByName('addNewCloudflareRecordPort')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordTargetLabel')[0].style.display = "none";
            document.getElementsByName('addNewCloudflareRecordTarget')[0].style.display = "none";
            document.getElementsByName('addNewCloudflareRecordTarget')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordContentLabel')[0].style.display = "none";
            document.getElementsByName('addNewCloudflareRecordContent')[0].style.display = "none";
            document.getElementsByName('addNewCloudflareRecordContent')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordTTLLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordTTL')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordTTL')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordvLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordvLabelsecond')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordvLabelv')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordv')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordv')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordpLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordpLabelp')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordp')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordp')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordspLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordspLabelsp')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordsp')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordsp')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordadkimLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordadkimLabeladkim')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordadkim')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordadkim')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordaspfLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordaspfLabelaspf')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordaspf')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordaspf')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordfoLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordfoLabelfo')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordfo')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordfo')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordrfLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordrfLabelrf')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordrf')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordrf')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordruaLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordruaLabelrua')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordrua')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordrua')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordrufLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordrufLabelruf')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordruf')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordruf')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordpctLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordpctLabelpct')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordpct')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordpct')[0].removeAttribute("required");

            document.getElementsByName('addNewCloudflareRecordriLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordriLabelri')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordri')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordri')[0].removeAttribute("required");


            document.getElementsByName('addNewCloudflareRecordPriorityLabel')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordPriority')[0].style.display = "block";
            document.getElementsByName('addNewCloudflareRecordSubmit')[0].style.display = "block";

        }

    }

    document.getElementsByClassName('pageTitle')[0].innerHTML = "Domain <?php echo $domain; ?>";
</script>

<style>
    .popupHidden {
        position: fixed;
        right: 19%;
        bottom: 0%;
        opacity: 0;
        width: 250px;
        height: 49px;
        z-index: 100;
        background-color: #1e5d8b;
        border-width: 1px;
        border-style: solid;
        overflow: hidden;
        color: whitesmoke;
        text-align: center;
        line-height: 50px;
    }

    .popupshowMe {
        -webkit-animation: popupShowAnimation 0.2s forwards;
        animation: popupShowAnimation 0.2s forwards;
    }

    .popuphideMe {
        -webkit-animation: popupHideAnimation 1s forwards;
        animation: popupHideAnimation 1s forwards;
    }

    @keyframes popupShowAnimation {
        0% {
            opacity: 0;
        }

        90% {
            opacity: 0;
        }

        100% {
            opacity: 1;
            display: block;
            width: 250px;
            height: 50px;
        }
    }

    @-webkit-keyframes popupShowAnimation {
        0% {
            opacity: 0;
        }

        90% {
            opacity: 0;
        }

        100% {
            opacity: 1;
            display: block;
            width: 250px;
            height: 50px;
        }
    }

    @keyframes popupHideAnimation {
        0% {
            opacity: 1;
            width: 250px;
            height: 50px;
        }

        90% {
            opacity: 1;
            width: 250px;
            height: 50px;
        }

        100% {
            width: 0;
            height: 0;
            display: none;
            opacity: 0;
        }
    }

    @-webkit-keyframes popupHideAnimation {
        0% {
            opacity: 1;
            width: 250px;
            height: 50px;
        }

        90% {
            opacity: 1;
            width: 250px;
            height: 50px;
        }

        100% {
            width: 0;
            height: 0;
            display: none;
            opacity: 0;
        }
    }


    .domainTypeFormat {
        display: inline;
        padding: .2em .6em .3em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: .25em;
    }

    .domainTypeSOA {
        background-color: #f0ad4e;
    }

    .domainTypeA {
        background-color: #337ab7;
    }

    .domainTypeAAAA {
        background-color: #5bc0de;
    }

    .domainTypeNS {
        background-color: #5cb85c;
    }

    .domainTypeMX {
        background-color: #d9534f;
    }

    .domainTypeTXT,
    .domainTypeDNSSEC,
    .domainTypeCNAME,
    .domainTypeSPF,
    .domainTypeDMARC,
    .domainTypeDKIM {
        background-color: #777
    }
</style>