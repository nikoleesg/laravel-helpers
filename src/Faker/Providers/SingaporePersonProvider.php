<?php

namespace Nikoleesg\LaravelHelpers\Faker\Providers;

use Faker\Provider\en_SG\Person;

class SingaporePersonProvider extends Person
{
    protected static $lastName = [
        'Tan', 'Lim', 'Lee', 'Ng', 'Ong', 'Wong', 'Goh', 'Chua', 'Chan', 'Koh',
        'Teo', 'Ang', 'Poh', 'Neo', 'Sim', 'Chong', 'Chia', 'Yeo', 'Tay', 'Low',
        'Toh', 'Choo', 'Chee', 'Cheong', 'Chew', 'Chin', 'Chow', 'Foo', 'Gan',
        'Heng', 'Ho', 'Khoo', 'Lau', 'Loh', 'Loo', 'Lum', 'Phua', 'Seah', 'Song',
        'Quek', 'Teng', 'Ting', 'Wee', 'Yap', 'Yong', 'Aw', 'Boon', 'Cheng'
    ];

    protected static $firstNameMale = [
        'Wei Jie', 'Jun Jie', 'Hao', 'Ming', 'Wei', 'Da', 'Qiang', 'Guo', 'An',
        'Gang', 'Bo', 'Wen', 'Chao', 'Cheng', 'Jian', 'Zhi', 'Hui', 'Xin', 'Long'
    ];

    protected static $firstNameFemale = [
        'Jing', 'Ting', 'Mei', 'Fang', 'Li', 'Min', 'Yan', 'Hua', 'Lan', 'Lian',
        'Ai', 'Yu', 'Shu', 'Qing', 'Na', 'Xia', 'Yun', 'Zhen', 'Ling', 'Xiu', 'Qun'
    ];

    protected static $firstNameMaleEn = [
        'Alex', 'Henry', 'Ethan', 'Noah', 'Lucas', 'Oliver', 'Liam', 'Jayden', 'Isaac', 'Benjamin', 
        'Caleb', 'Daniel', 'Joshua', 'Jacob', 'John', 'Jonathan', 'Josiah', 'Jovan', 'Justin',
        'Ayden', 'Kayden', 'Eden', 'Hayden', 'Shawn', 'Shaun', 'Sean', 'Vincent', 'Desmond',
        'Alvin', 'Kelvin', 'Melvin', 'Bryan', 'Ryan', 'Joel', 'Leon', 'Marcus', 'Gideon'
    ];

    protected static $firstNameFemaleEn = [
        'Julyn', 'Michelle', 'Sarah', 'Ashley', 'Olivia', 'Chloe', 'Sophia', 'Emma', 'Charlotte', 
        'Amelia', 'Harper', 'Evelyn', 'Haley', 'Hailey', 'Hana', 'Hannah', 'Hazel', 'Heather', 
        'Heidi', 'Hope', 'Amanda', 'Melissa', 'Rachel', 'Jessica', 'Nicole', 'Jasmine', 'Shirley', 
        'Grace', 'Joanne', 'Felicia', 'Charmaine', 'Eunice', 'Valerie', 'Bernice', 'Serene'
    ];

    protected static $formats = [
        '{{lastName}} {{firstName}}',
        '{{firstNameEn}} {{lastName}}',
        '{{firstNameEn}} {{lastName}} {{firstName}}',
    ];

    protected static $maleNameFormats = [
        '{{lastName}} {{firstNameMale}}',
        '{{firstNameMaleEn}} {{lastName}}',
        '{{firstNameMaleEn}} {{lastName}} {{firstNameMale}}',
    ];

    protected static $femaleNameFormats = [
        '{{lastName}} {{firstNameFemale}}',
        '{{firstNameFemaleEn}} {{lastName}}',
        '{{firstNameFemaleEn}} {{lastName}} {{firstNameFemale}}',
    ];

    public function firstNameMaleEn()
    {
        return static::randomElement(static::$firstNameMaleEn);
    }

    public function firstNameFemaleEn()
    {
        return static::randomElement(static::$firstNameFemaleEn);
    }

    public function firstNameEn()
    {
        return static::randomElement(array_merge(static::$firstNameMaleEn, static::$firstNameFemaleEn));
    }
}
