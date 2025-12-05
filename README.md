# Rubinault
Web interface of the Renault API to manage your car remotely. It runs under PHP8.3 or higher and a web server like Apache2 or Nginx.

For the moment, this ONLY WORKS FOR FRENCH USERS as Renault decided to use different domains depending on the country, instead a unified one.

Rubinault uses the username and password that you previously created in the application MyRenault or the website https://myr.renault.fr
Once you have your credentials, Rubinault will act as a remote client using the commands as defined at https://renault-api.readthedocs.io/en/latest/

## How it works?
First, the user obtains a token from the Renault server, sending the credentials to the Gygia server https://accounts.eu1.gigya.com
Once the user is identified, Rubinault connects to the account and collects the declared vehicles and its specifications.

Depending on the type of vehicle, the architecture is different and the available remote commands as well. At this moment, I only create the architecture for:

- Mégane IV (not connected)
- Clio V (basic connectivity)
- New R4 EV (full connectivity)

The architecture for each vehicle is stored in a json file at the folder /mapping. This can change also depending on the changes performed by Renault to use the remote commands.

### What can I do with Rubinault?
Depending on the type of vehicle, you can manage some things or others.
For old ICE cars (interal combustion engines) not having any connectivity, the only available information is about the contract Renault
For more recent cars (since 2018) for vehicles having a limited connectivity, only the function Carfinder (location) is available
For EV cars (since 2021) for vehivles havinf a full connectivity, some extended functions like
- Remote HVAC
- Remote Horn & Light
- EV Charge planning
- EV Charge history
- EV Charge settings

### What I cannot do?
Obviously, Rubinault has some limitations, because it uses the Renault architecture to send MQTT remote commands.
At this moment I did not succeed to emulate the complete functions that are available in the original APK MyRenault.

## Configuration
The configuration is located in the file configuration.php

```
class RbnoConfig
{
        public $sitename        = 'Rubinault';
        public $live_site       = 'https://mywebsite.com/rubinault';
        public $log_path        = '/var/www/rubinault/log';
        public $tmp_path        = '/var/www/rubinault/tmp';
        public $debug           = false;
        public $test            = false;
        public $use_cache       = true;
        public $anti_throttle   = 300;
        public $key             = 'x28DL"?(xu`"N%st4E[JosX\d$iHu:|J%.S_L7boav0bX:yFS`sEH0gw-d=/&%Oq';
        public $list_limit      = 36;
        public $slider_limit    = 20;
        public $theme           = 'default';   
        public $gigya           = [ 
                'country'       => 'FR',
                'url'           => 'https://accounts.eu1.gigya.com',
                'apikey'        => '3_e8d4g4SE_Fo8ahyHwwP7ohLGZ79HKNN2T8NjQqoNnk6Epj6ilyYwKdHUyCw3wuxz'
        ];
        public $wired           = [
                'country'       => 'FR',
                'locale'        => 'fr_FR',
                'url'           => 'https://api-wired-prod-1-euw1.wrd-aws.com',
                'apikey'        => 'YjkKtHmGfaceeuExUDKGxrLZGGvtVS0J'
        ];            
}
```



