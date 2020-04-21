<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\orderform\models;

use craft\base\Model;
use craft\web\UploadedFile;

/**
 * Class Submission
 *
 * @package craft\orderform
 */
class Submission extends Model
{
    /**
     * @var string|null
     */
    public $firstName;

    /**
     * @var string|null
     */
    public $lastName;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string
     */
    public $birthday;

    /**
     * @var string
     */
    public $gender;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'firstName' => \Craft::t('contact-form', 'Vorname'),
            'lastName' => \Craft::t('contact-form', 'Nachname'),
            'email' => \Craft::t('contact-form', 'E-Mail'),
            'birthday' => \Craft::t('contact-form', 'Geburtstag'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['firstName', 'lastName', 'birthday', ], 'required'],
            [['email'], 'email'],
            [['birthday'], 'datetime']
        ];
    }
}
