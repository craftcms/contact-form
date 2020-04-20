<?php

namespace craft\orderform\controllers;

use Craft;
use craft\orderform\models\Submission;
use craft\orderform\Plugin;
use craft\web\Controller;
use craft\web\UploadedFile;
use yii\web\Response;
use GuzzleHttp\Client;


class SendController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * Sends a contact form submission.
     *
     * @return Response|null
     */
    public function actionIndex()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $plugin = Plugin::getInstance();
        $settings = $plugin->getSettings();

        $submission = new Submission();
        $submission->firstName = $request->getBodyParam('firstName');
        $submission->lastName = $request->getBodyParam('lastName');
        $submission->email = $request->getBodyParam('email');
        $submission->birthday = $request->getBodyParam('birthday');

        $shit = json_encode($submission);
/*
        if (!$plugin->getMailer()->send($submission)) {
            if ($request->getAcceptsJson()) {
                return $this->asJson(['errors' => $submission->getErrors()]);
            }

            Craft::$app->getSession()->setError(Craft::t('contact-form', 'There was a problem with your submission, please check the form and try again!'));
            Craft::$app->getUrlManager()->setRouteParams([
                'variables' => ['message' => $submission]
            ]);

            return null;
        }
*/
        // return null;
        if ($request->getAcceptsJson()) {
            return $this->asJson(['success' => true]);
        }

        Craft::$app->getSession()->setNotice($settings->successFlashMessage);
        return $this->redirectToPostedUrl($submission);
    }
}
