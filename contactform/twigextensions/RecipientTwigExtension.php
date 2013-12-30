<?php
namespace Craft;

class RecipientTwigExtension extends \Twig_Extension
{
    protected $env;
    
    public function getName()
    {
        return 'Adds additional recipients to contact form';
    }
    
    public function getFilters()
    {
        return array('contact_recipient' => new \Twig_Filter_Method($this, 'add_recipient', array('is_safe' => array('html'))));
    }
    
    public function getFunctions()
    {
        return array('contact_recipient' => new \Twig_Function_Method($this, 'add_recipient', array('is_safe' => array('html'))));
    }
    
    public function initRuntime(\Twig_Environment $env)
    {
        $this->env = $env;
    }
    
    public function add_recipient($email)
    {

        $email = '<input type="hidden" name="recipients[]" value="' . base64_encode(\Yii::app()->getSecurityManager()->encrypt($email)) . '">'; 

        return $email;
    }
}