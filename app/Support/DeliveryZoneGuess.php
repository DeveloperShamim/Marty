<?php

namespace App\Support;

/**
 * Guesses the checkout delivery zone from the address and city a buyer types.
 * A district or town outside Dhaka city wins (it is the more specific clue, e.g. "Mirpur, Kushtia"),
 * then a Dhaka city area or "Dhaka" itself. No clue → no zone, and the buyer picks one.
 * Savar, Keraniganj, Gazipur, Narayanganj and the like count as outside: couriers charge them as outside the city.
 * The checkout page runs the same lists in JavaScript (see toJs()).
 */
class DeliveryZoneGuess
{
    /** Areas of Dhaka city, English and Bangla. */
    public const INSIDE = [
        'dhaka', 'dhaka city', 'mirpur', 'pallabi', 'kafrul', 'dhaka cantonment', 'uttara', 'uttarkhan', 'dakshinkhan',
        'khilkhet', 'nikunja', 'gulshan', 'banani', 'baridhara', 'bashundhara', 'badda', 'rampura', 'banasree', 'banasri',
        'aftabnagar', 'khilgaon', 'basabo', 'sabujbagh', 'mugda', 'motijheel', 'paltan', 'kakrail', 'shantinagar', 'malibagh',
        'mogbazar', 'moghbazar', 'eskaton', 'ramna', 'shahbag', 'segunbagicha', 'tejgaon', 'tejgaon industrial area', 'farmgate',
        'karwan bazar', 'kawran bazar', 'kawranbazar', 'panthapath', 'kalabagan', 'dhanmondi', 'jigatola', 'hazaribagh',
        'mohammadpur', 'mohammedpur', 'adabor', 'shyamoli', 'shymoli', 'agargaon', 'sher-e-bangla nagar', 'sher e bangla nagar',
        'kallyanpur', 'kalyanpur', 'gabtoli', 'darussalam', 'shah ali', 'rupnagar', 'mohakhali', 'niketan', 'vatara', 'bhatara',
        'kuril', 'jatrabari', 'demra', 'shyampur', 'kadamtali', 'sutrapur', 'wari', 'gendaria',
        'lalbagh', 'chawkbazar', 'chowkbazar', 'bangshal', 'azimpur', 'nilkhet', 'elephant road',
        'puran dhaka', 'old dhaka', 'gulistan', 'sayedabad', 'jurain', 'postogola', 'turag', 'diabari', 'tongi diversion',
        'malibag', 'baily road', 'siddheswari', 'mintu road', 'hatirpool', 'hatirjheel', 'banglamotor', 'shukrabad', 'lalmatia',
        'rayer bazar', 'beribadh', 'mohakhali dohs', 'banani dohs', 'mirpur dohs', 'baridhara dohs', 'kazipara', 'shewrapara',
        'ibrahimpur', 'manikdi', 'matikata', 'mohammadia housing', 'bosila', 'dholaipar', 'shonir akhra', 'matuail',
        'ঢাকা', 'মিরপুর', 'পল্লবী', 'কাফরুল', 'উত্তরা', 'খিলক্ষেত', 'নিকুঞ্জ', 'গুলশান', 'বনানী', 'বারিধারা',
        'বসুন্ধরা', 'বাড্ডা', 'রামপুরা', 'বনশ্রী', 'আফতাবনগর', 'খিলগাঁও', 'বাসাবো', 'সবুজবাগ', 'মুগদা', 'মতিঝিল', 'পল্টন',
        'কাকরাইল', 'শান্তিনগর', 'মালিবাগ', 'মগবাজার', 'রমনা', 'শাহবাগ', 'তেজগাঁও', 'ফার্মগেট', 'কারওয়ান বাজার', 'পান্থপথ',
        'কলাবাগান', 'ধানমন্ডি', 'হাজারীবাগ', 'মোহাম্মদপুর', 'আদাবর', 'শ্যামলী', 'আগারগাঁও', 'কল্যাণপুর', 'গাবতলী', 'মহাখালী',
        'ভাটারা', 'কুড়িল', 'যাত্রাবাড়ী', 'ডেমরা', 'শ্যামপুর', 'সূত্রাপুর', 'ওয়ারী', 'গেন্ডারিয়া', 
        'লালবাগ', 'চকবাজার', 'বংশাল', 'আজিমপুর', 'নীলক্ষেত', 'পুরান ঢাকা', 'গুলিস্তান', 'সায়েদাবাদ', 'তুরাগ',
        'দক্ষিণখান', 'উত্তরখান', 'লালমাটিয়া', 'রায়ের বাজার', 'কাজীপাড়া', 'শেওড়াপাড়া', 'মাটিকাটা',
    ];

    /** Every district except Dhaka, divisions, common old spellings, and the Dhaka suburbs couriers charge as outside. */
    public const OUTSIDE = [
        'bagerhat', 'bandarban', 'barguna', 'barisal', 'barishal', 'bhola', 'bogra', 'bogura', 'brahmanbaria', 'b.baria', 'chandpur',
        'chapai nawabganj', 'chapainawabganj', 'nawabganj', 'chattogram', 'chittagong', 'ctg', 'chuadanga', 'comilla', 'cumilla',
        "cox's bazar", 'coxs bazar', 'cox bazar', 'coxsbazar', 'dinajpur', 'faridpur', 'feni', 'gaibandha', 'gazipur', 'gopalganj',
        'habiganj', 'jamalpur', 'jessore', 'jashore', 'jhalokati', 'jhalokathi', 'jhenaidah', 'joypurhat', 'jaipurhat', 'khagrachari',
        'khagrachhari', 'khulna', 'kishoreganj', 'kishorganj', 'kurigram', 'kushtia', 'lakshmipur', 'laxmipur', 'lalmonirhat',
        'madaripur', 'magura', 'manikganj', 'meherpur', 'moulvibazar', 'maulvibazar', 'munshiganj', 'mymensingh', 'naogaon', 'narail',
        'narayanganj', 'narsingdi', 'natore', 'netrokona', 'netrakona', 'nilphamari', 'noakhali', 'pabna', 'panchagarh', 'patuakhali',
        'pirojpur', 'rajbari', 'rajshahi', 'rangamati', 'rangpur', 'satkhira', 'shariatpur', 'sherpur', 'sirajganj', 'sunamganj',
        'sylhet', 'tangail', 'thakurgaon',
        'savar', 'ashulia', 'keraniganj', 'dohar', 'nawabganj dhaka', 'dhamrai', 'tongi', 'board bazar', 'joydebpur', 'kaliakair',
        'sreepur', 'kaliganj', 'siddhirganj', 'fatullah', 'rupganj', 'sonargaon', 'araihazar', 'kanchpur', 'purbachal', 'hemayetpur',
        'ঢাকার বাইরে', 'বাগেরহাট', 'বান্দরবান', 'বরগুনা', 'বরিশাল', 'ভোলা', 'বগুড়া', 'ব্রাহ্মণবাড়িয়া', 'চাঁদপুর', 'চাঁপাইনবাবগঞ্জ',
        'চট্টগ্রাম', 'চুয়াডাঙ্গা', 'কুমিল্লা', 'কক্সবাজার', 'দিনাজপুর', 'ফরিদপুর', 'ফেনী', 'গাইবান্ধা', 'গাজীপুর', 'গোপালগঞ্জ',
        'হবিগঞ্জ', 'জামালপুর', 'যশোর', 'ঝালকাঠি', 'ঝিনাইদহ', 'জয়পুরহাট', 'খাগড়াছড়ি', 'খুলনা', 'কিশোরগঞ্জ', 'কুড়িগ্রাম',
        'কুষ্টিয়া', 'লক্ষ্মীপুর', 'লালমনিরহাট', 'মাদারীপুর', 'মাগুরা', 'মানিকগঞ্জ', 'মেহেরপুর', 'মৌলভীবাজার', 'মুন্সিগঞ্জ',
        'ময়মনসিংহ', 'নওগাঁ', 'নড়াইল', 'নারায়ণগঞ্জ', 'নরসিংদী', 'নাটোর', 'নেত্রকোনা', 'নীলফামারী', 'নোয়াখালী', 'পাবনা',
        'পঞ্চগড়', 'পটুয়াখালী', 'পিরোজপুর', 'রাজবাড়ী', 'রাজশাহী', 'রাঙ্গামাটি', 'রংপুর', 'সাতক্ষীরা', 'শরীয়তপুর', 'শেরপুর',
        'সিরাজগঞ্জ', 'সুনামগঞ্জ', 'সিলেট', 'টাঙ্গাইল', 'ঠাকুরগাঁও', 'সাভার', 'আশুলিয়া', 'কেরানীগঞ্জ', 'টঙ্গী', 'ধামরাই', 'দোহার',
        'ফতুল্লা', 'সিদ্ধিরগঞ্জ', 'রূপগঞ্জ', 'সোনারগাঁও',
    ];

    /** 'inside_dhaka', 'outside_dhaka' or null when the address gives no clue. */
    public static function guess(?string ...$parts): ?string
    {
        $text = self::normalize(implode(' , ', array_filter($parts, fn ($p) => $p !== null)));
        if ($text === '') {
            return null;
        }
        if (self::mentions($text, self::OUTSIDE)) {
            return 'outside_dhaka';
        }

        return self::mentions($text, self::INSIDE) ? 'inside_dhaka' : null;
    }

    public static function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = str_replace(['’', '‘'], "'", $text);

        // Punctuation and runs of space become one space, so words can be matched whole
        return trim(preg_replace('/[\s,.;:\/\\\\()\[\]#\-–—|]+/u', ' ', $text));
    }

    private static function mentions(string $text, array $names): bool
    {
        $padded = ' ' . $text . ' ';
        foreach ($names as $name) {
            $name = self::normalize($name);
            // Bangla is matched as written (it carries no spaces-as-word-ends we can trust); English as whole words
            if (preg_match('/\p{Bengali}/u', $name) ? str_contains($padded, $name) : str_contains($padded, ' ' . $name . ' ')) {
                return true;
            }
        }

        return false;
    }

    /** Lists for the checkout script, already normalised. */
    public static function toJs(): array
    {
        return [
            'inside'  => array_values(array_unique(array_map([self::class, 'normalize'], self::INSIDE))),
            'outside' => array_values(array_unique(array_map([self::class, 'normalize'], self::OUTSIDE))),
        ];
    }
}
