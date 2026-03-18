<?php

namespace Nikoleesg\LaravelHelpers\Faker\Providers;

use Faker\Provider\en_SG\Address;
use Nikoleesg\LaravelHelpers\Enums\HouseType;

class SingaporeAddressProvider extends Address
{
    public ?HouseType $houseType = null;

    protected static $streetNumber = ['##', '###'];

    protected static $streetSuffix = [
        'Alley', 'Avenue',
        'Bridge',
        'Crescent',
        'Drive',
        'Grove',
        'Highway', 'Hill',
        'Lane', 'Link',
        'Park', 'Place',
        'Quay',
        'Road',
        'Walk', 'Way',
    ];

    protected static $streetPrefix = [
        'Jalan',
    ];

    protected static $streetName = [
        'Adam', 'Airport', 'Alexandra', 'Aljunied', 'Ampang', 'Ann Siang', 'Angus', 'Anson', 'Armenian',
        'Balmoral', 'Battery', 'Bencoolen',
        'Collyer', 'Clarke', 'Church', 'Cecil', 'Cross', 'Chulia', 'Cheang Hong Lim', 'Chin Swee', 'Choon Guan',
        'Devonshire', 'Dublin', 'Duxton', 'D\'Almeida',
        'East Coast', 'Eden', 'Edgware', 'Eunos',
        'Fifth', 'First', 'Funan', 'Fullerton',
        'George', 'Glasgow', 'Grange',
        'Havelock', 'High', 'Hylam',
        'International Business', 'International', 'Irving',
        'Jubilee',
        'Kensington Park', 'Kitchener', 'Knights',
        'Lancaster', 'Leicester', 'Lengkok Bahru', 'Lim Teck Kim',
        'Malay', 'Market', 'Middle', 'Malabar', 'Merchant', 'Mohammed Sultan',
        'Napier', 'Nathan', 'Newton',
        'Ocean', 'One Tree', 'Orchard', 'Outram', 'Ophir',
        'Pekin', 'Peng Siang', 'Prince Edward', 'Palmer',
        'Quality', 'Queen',
        'Raffles', 'Robinson', 'Rochor', 'Regent', 'Ridley', 'River Valley',
        'Sixth', 'Somerset', 'Stanley', 'Stamford', 'Shenton', 'Sultan',
        'Telok Ayer', 'Temple', 'Thomson', 'Unity', 'Victoria', 'Xilin', 'York', 'Zion',
    ];

    protected static $streetAddressFormats = [
        '{{streetPrefix}} {{streetName}}',
        '{{streetName}} {{streetSuffix}}',
        '{{streetName}} {{streetSuffix}} {{streetNumber}}',
    ];

    protected static $floorNumber = [
        '##', '0#',
    ];

    // Singapore apartments typically are 2-4 digits.
    protected static $apartmentNumber = [
        '##', '###', '####'
    ];

    public function setHouseType(?HouseType $houseType): self
    {
        $this->houseType = $houseType;

        return $this;
    }

    public function streetPrefix()
    {
        return static::randomElement(static::$streetPrefix);
    }

    public function streetNumber()
    {
        return static::numerify(static::randomElement(static::$streetNumber));
    }

    public function streetName()
    {
        return static::randomElement(static::$streetName);
    }

    /**
     * Override block generation to match custom HDB and Condominium logic. 
     */
    public function blockNumber()
    {
        $block = (string) $this->generator->numberBetween(1, 999);

        // HDB properties have a 20% chance of appending a block letter
        if ($this->houseType === HouseType::HDB && $this->generator->boolean(20)) {
            $block .= $this->generator->randomLetter();
        }

        return strtoupper($block);
    }

    /**
     * Use provider's floor and apartment formats to generate the full unit string.
     */
    public function unitNumber()
    {
        if ($this->houseType === HouseType::Landed) {
            return null;
        }

        $floor = static::numerify(static::randomElement(static::$floorNumber));
        $apartment = static::numerify(static::randomElement(static::$apartmentNumber));

        return sprintf('#%s-%s', $floor, $apartment);
    }
}
