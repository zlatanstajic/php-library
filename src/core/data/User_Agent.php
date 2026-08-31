<?php

/**
 * User_Agent
 *
 * Working with user agent related data
 *
 * @author       Zlatan Stajić <contact@zlatanstajic.com>
 */

namespace PHP_Library\Core\Data;

/**
 * Working with user agent related data
 *
 * @deprecated User-agent sniffing is unreliable. Prefer feature detection or
 *             a maintained parser when identification is unavoidable.
 */
class User_Agent
{
    /**
     * List of browsers and signatures
     *
     * @var list<array{name: string, signature: list<string>}>
     */
    private const array BROWSERS = [
        [
            'name' => 'Edge',
            'signature' => [
                'Edg',
                'Edge',
            ],
        ],
        [
            'name' => 'Opera',
            'signature' => [
                'OPR',
                'Opera',
            ],
        ],
        [
            'name' => 'Firefox',
            'signature' => [
                'Firefox',
                'FxiOS',
            ],
        ],
        [
            'name' => 'Chrome',
            'signature' => [
                'Chrome',
                'CriOS',
            ],
        ],
        [
            'name' => 'Safari',
            'signature' => [
                'Safari',
            ],
        ],
        [
            'name' => 'Explorer',
            'signature' => [
                'MSIE',
                'Trident',
            ],
        ],
    ];

    /**
     * List operating systems and signatures
     *
     * @var list<array{regex: string, name: string, group: string}>
     */
    private const array OPERATING_SYSTEMS = [
        [
            'regex' => '/windows nt 10.0/i',
            'name' => 'Windows 10',
            'group' => 'Windows',
        ],
        [
            'regex' => '/windows nt 6.2/i',
            'name' => 'Windows 8',
            'group' => 'Windows',
        ],
        [
            'regex' => '/windows nt 6.1/i',
            'name' => 'Windows 7',
            'group' => 'Windows',
        ],
        [
            'regex' => '/windows nt 6.0/i',
            'name' => 'Windows Vista',
            'group' => 'Windows',
        ],
        [
            'regex' => '/windows nt 5.2/i',
            'name' => 'Windows Server 2003/XP x64',
            'group' => 'Windows',
        ],
        [
            'regex' => '/windows nt 5.1/i',
            'name' => 'Windows XP',
            'group' => 'Windows',
        ],
        [
            'regex' => '/windows xp/i',
            'name' => 'Windows XP',
            'group' => 'Windows',
        ],
        [
            'regex' => '/windows nt 5.0/i',
            'name' => 'Windows 2000',
            'group' => 'Windows',
        ],
        [
            'regex' => '/windows me/i',
            'name' => 'Windows ME',
            'group' => 'Windows',
        ],
        [
            'regex' => '/win98/i',
            'name' => 'Windows 98',
            'group' => 'Windows',
        ],
        [
            'regex' => '/win95/i',
            'name' => 'Windows 95',
            'group' => 'Windows',
        ],
        [
            'regex' => '/win16/i',
            'name' => 'Windows 3.11',
            'group' => 'Windows',
        ],
        [
            'regex' => '/macintosh|mac os x/i',
            'name' => 'Mac OS X',
            'group' => 'Macintosh',
        ],
        [
            'regex' => '/mac_powerpc/i',
            'name' => 'Mac OS 9',
            'group' => 'Macintosh',
        ],
        [
            'regex' => '/iphone/i',
            'name' => 'iPhone',
            'group' => 'iOS',
        ],
        [
            'regex' => '/ipod/i',
            'name' => 'iPod',
            'group' => 'iOS',
        ],
        [
            'regex' => '/ipad/i',
            'name' => 'iPad',
            'group' => 'iOS',
        ],
        [
            'regex' => '/linux/i',
            'name' => 'Linux',
            'group' => 'Linux',
        ],
        [
            'regex' => '/ubuntu/i',
            'name' => 'Ubuntu',
            'group' => 'Linux',
        ],
        [
            'regex' => '/android/i',
            'name' => 'Android',
            'group' => 'Android',
        ],
        [
            'regex' => '/blackberry/i',
            'name' => 'BlackBerry',
            'group' => 'BlackBerry',
        ],
    ];

    /**
     * List of devices and signatures
     *
     * @var list<array{name: string, signature: list<string>}>
     */
    private const array DEVICES = [
        [
            'name' => 'Windows',
            'signature' => [
                'Windows',
            ],
        ],
        [
            'name' => 'Android',
            'signature' => [
                'Android',
            ],
        ],
        [
            'name' => 'iPhone',
            'signature' => [
                'iPhone',
            ],
        ],
    ];

    /**
     * List of crawlers user agents
     *
     * @var list<string>
     */
    private const array CRAWLERS = [
        'Mozilla/5.0 (compatible; AhrefsBot/5.2; +http://ahrefs.com/robot/)',
        'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
        'Mozilla/5.0 (compatible; Google-Site-Verification/1.0)',
        'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        'Mozilla/5.0 (compatible; GrapeshotCrawler/2.0; +http://www.grapeshot.co.uk/crawler.php)',
        'Mozilla/5.0 (compatible; Linux x86_64; Mail.RU_Bot/2.0; +http://go.mail.ru/help/robots)',
        'Mozilla/5.0 (compatible; Linux x86_64; Mail.RU_Bot/Robots/2.0; +http://go.mail.ru/help/robots)',
        'Mozilla/5.0 (compatible; MJ12bot/v1.4.7; http://mj12bot.com/)',
        'Mozilla/5.0 (compatible; MJ12bot/v1.4.8; http://mj12bot.com/)',
        'Mozilla/5.0 (compatible; NetcraftSurveyAgent/1.0; +info@netcraft.com)',
        'Mozilla/5.0 (compatible; SeznamBot/3.2; +http://napoveda.seznam.cz/en/seznambot-intro/)',
        'Mozilla/5.0 (compatible; Uptimebot/1.0; +http://www.uptime.com/uptimebot)',
        'Mozilla/5.0 (compatible; Windows; U; Windows NT 6.2; en-US; rv:12.0) Gecko/20120403211507 Firefox/12.0',
        'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)',
        'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)',
        'Mozilla/5.0 (compatible; YandexImages/3.0; +http://yandex.com/bots)',
        'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
        'Mozilla/5.0 (compatible; ips-agent)',
        'SafeDNSBot (https://www.safedns.com/searchbot)',
        'Mozilla/5.0 zgrab/0.x',
        'Googlebot-Image/1.0',
        'Twitterbot/1.0',
        'bitlybot/3.0 (+http://bit.ly/)',
        'curl/7.54.0',
        'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
        'python-requests/2.18.4',
    ];

    /**
     * Mobile regex
     */
    private const string MOBILE_USER_AGENT_ONE = '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i';

    /**
     * Mobile regex
     */
    private const string MOBILE_USER_AGENT_TWO = '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i';

    /**
     * List all browsers
     *
     * @return list<array{name: string, signature: list<string>}>
     */
    public static function list_browsers(): array
    {
        return self::BROWSERS;
    }

    /**
     * List all operating systems
     *
     * Keys are preserved by asort(), so this is a keyed array, not a list.
     *
     * @return array<int, string>
     */
    public static function list_operating_systems(bool $only_group = false): array
    {
        $operating_systems = [];

        $key = $only_group
            ? 'group'
            : 'name';

        foreach (self::OPERATING_SYSTEMS as $item) {
            if (! in_array($item[$key], $operating_systems)) {
                $operating_systems[] = $item[$key];
            }
        }

        asort($operating_systems);

        return $operating_systems;
    }

    /**
     * List all devices
     *
     * @return list<array{name: string, signature: list<string>}>
     */
    public static function list_devices(): array
    {
        return self::DEVICES;
    }

    /**
     * List all crawlers
     *
     * @return list<string>
     */
    public static function list_crawlers(): array
    {
        return self::CRAWLERS;
    }

    /**
     * Detects browser according to user agent
     *
     * More specific Chromium-family signatures are ordered before Chrome.
     */
    public static function detect_browser(string $user_agent, string $name_when_no_match = ''): string
    {
        foreach (self::BROWSERS as $browser) {
            foreach ($browser['signature'] as $signature) {
                if (stripos($user_agent, $signature) !== false) {
                    return $browser['name'];
                }
            }
        }

        return $name_when_no_match;
    }

    /**
     * Detects operating system according to user agent
     *
     * @return array{regex: string, name: string, group: string}
     */
    public static function detect_operating_system(string $user_agent, string $name_when_no_match = ''): array
    {
        foreach ([
            ['regex' => '/iphone/i', 'name' => 'iPhone', 'group' => 'iOS'],
            ['regex' => '/ipod/i', 'name' => 'iPod', 'group' => 'iOS'],
            ['regex' => '/ipad/i', 'name' => 'iPad', 'group' => 'iOS'],
            ['regex' => '/android/i', 'name' => 'Android', 'group' => 'Android'],
            ['regex' => '/ubuntu/i', 'name' => 'Ubuntu', 'group' => 'Linux'],
        ] as $item) {
            if (preg_match($item['regex'], $user_agent)) {
                return $item;
            }
        }

        foreach (self::OPERATING_SYSTEMS as $item) {
            if (preg_match($item['regex'], $user_agent)) {
                return $item;
            }
        }

        return [
            'regex' => '',
            'name' => $name_when_no_match,
            'group' => '',
        ];
    }

    /**
     * Detects device according to user agent
     *
     * Signatures are matched case-insensitively, including at offset zero.
     */
    public static function detect_device(string $user_agent, string $name_when_no_match = ''): string
    {
        foreach (self::DEVICES as $device) {
            foreach ($device['signature'] as $signature) {
                if (stripos($user_agent, $signature) !== false) {
                    return $device['name'];
                }
            }
        }

        return $name_when_no_match;
    }

    /**
     * Determines if given user agent is from mobile device
     */
    public static function is_mobile(string $user_agent): bool
    {
        if (
            preg_match(self::MOBILE_USER_AGENT_ONE, $user_agent) ||
            preg_match(self::MOBILE_USER_AGENT_TWO, substr($user_agent, 0, 4))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determines if given user agent is crawler or not
     */
    public static function is_crawler(string $user_agent): bool
    {
        if (in_array($user_agent, self::CRAWLERS, true)) {
            return true;
        }

        return preg_match(
            '/(?:bot\b|crawler|spider|slurp|facebookexternalhit|zgrab|curl\/|python-requests)/i',
            $user_agent
        ) === 1;
    }
}
