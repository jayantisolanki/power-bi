<!DOCTYPE html>
<html>

<head>
    <title>Reports</title>
    <script src="js/jquery-3.6.3.min.js"></script>
    <script src="js/powerbi.min.js"></script>
</head>

<body>
    <?php        
        /* Get oauth2 token using a POST request */
        $curlPostToken = curl_init();
        $clientId = "";
        $clientSecretId = "";
        $tenantId = '';
        $workspace = "";
        $ReportId = "";
        
        curl_setopt_array($curlPostToken, array(
            CURLOPT_URL => "https://login.microsoftonline.com/$tenantId/oauth2/token",            
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array(
                'grant_type' => 'client_credentials',
                'scope' => '.default',
                'resource' => 'https://analysis.windows.net/powerbi/api',
                'tenant_id' => $tenantId,                
                'client_id' => $clientId, // Registered App Application ID                
                'client_secret' => $clientSecretId,                
            )
        ));

        $tokenResponse = curl_exec($curlPostToken);
        $tokenError = curl_error($curlPostToken);

        echo $tokenError;        
        // decode result, and store the access_token in $embeddedToken variable:
        $tokenResult = json_decode($tokenResponse, true);
        $token = $tokenResult["access_token"];
        $bearerToken = "Bearer "  . ' ' .  $token;
       
        $post_params = array(
            "reports" => Array(                
                Array('id'=>$ReportId),

            ),
            "accessLevel"=>"View",
        );

        $payload = json_encode($post_params);        
        
        //Get embeded token

        $curlGetEmbededToken = curl_init();

        curl_setopt_array($curlGetEmbededToken, array(

            // Make changes Start
            CURLOPT_URL => "https://api.powerbi.com/v1.0/myorg/groups/$workspace/reports/$ReportId/GenerateToken",
            //CURLOPT_URL => "https://api.powerbi.com/v1.0/myorg/GenerateToken", // Enter your Workspace ID, aka Group ID
            // Make changes End

            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: $bearerToken",
            ),
            CURLOPT_POSTFIELDS => $payload

        ));

        $embedResponse = curl_exec($curlGetEmbededToken);
        $embedError = curl_error($curlGetEmbededToken);
                
        echo $embedError;        

        if ($embedError) {
            echo "cURL Error #:" . $embedError;
        } else {
            $embedResponse = json_decode($embedResponse, true);
            $embededToken = $embedResponse['token'];            
        }

        //get emebede url
        $curlGetUrl = curl_init();
        curl_setopt_array($curlGetUrl, array(

            // Make changes Start
            CURLOPT_URL => "https://api.powerbi.com/v1.0/myorg/groups/$workspace/reports/$ReportId", // Enter your Workspace ID, aka Group ID
            // Make changes End

            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: $bearerToken",
            ),

        ));
        $embedResponse = curl_exec($curlGetUrl);
        $embedError = curl_error($curlGetUrl);        
        echo $embedError;
        //curl_close($$curlGetUrl);
        if ($embedError) {
            echo "cURL Error #:" . $embedError;
        } else {
            $embedResponse = json_decode($embedResponse, true);
            $embedUrl = $embedResponse['embedUrl'];            
        }
        ?>

    <div id="reportContainer" style="height:100vh; width:100%;">
    </div>
    <script>
    // Get models. models contains enums that can be used.
    var models = window['powerbi-client'].models;

    var embedConfiguration = {
        type: 'report',
        tokenType: models.TokenType.Embed,
        embedUrl: '<?php echo $embedUrl ?>',
        accessToken: '<?php echo $embededToken; ?>',
        id: '<?php echo $ReportId ?>',
        permissions: models.Permissions.All,
        settings: {
            panes: {
                filters: {
                    visible: false
                },
                pageNavigation: {
                    visible: true
                }
            }
        }
    };
    var $reportContainer = $('#reportContainer');
    var report = powerbi.embed($reportContainer.get(0), embedConfiguration);
    </script>

</body>

</html>