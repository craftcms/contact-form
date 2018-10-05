<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\contactform\models;

use Craft;
use craft\base\Model;
use craft\web\UploadedFile;
use craft\web\Request;
use craft\contactform\Plugin;

/**
 * Class Submission
 *
 * @package craft\contactform
 */
interface SubmissionInterface
{
    /**
     * Populates the model with params from request
     * 
     * @return Submission model
     */
    public function populateModel(Request $request): Submission;

    /**
     * Compiles the "From" name value from the submitted name.
     *
     * @return string
     */
    public function compileFromName(): string;

    /**
     * Compiles the real email subject from the submitted subject.
     *
     * @return string
     */
    public function compileSubject(): string;

    /**
     * Compiles the real email textual body from the submitted message.
     *
     * @param Submission $submission
     * @return string
     */
    public function compileTextBody(): string;

    /**
     * @inheritdoc
     */
    public function attributeLabels();

    /**
     * @inheritdoc
     */
    public function rules();

}