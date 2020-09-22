<?php
/**
 * 2015-2020 AdScale LTD
 *
 * @author    AdScale LTD <support@adscale.com>
 * @copyright 2015-2020 AdScale LTD
 * @license  according to AdScale license terms & conditions - https://ecom.adscale.com/pricing/
 */

namespace AdScale\Exception;

class AdScaleExeption extends \Exception
{
    
    protected $action;
    protected $subject;
    protected $message_front;
    
    
    
    public function __construct(
        $message,
        $code,
        \Exception $previous = null,
        $message_front = '',
        $action = '',
        $subject = ''
    ) {
        
        $this->action        = $action;
        $this->subject       = $subject;
        $this->message_front = $message_front;
        
        parent::__construct($message, $code, $previous);
    }
    
    
    
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
    
    
    public function toLog($one_line = true)
    {
        if ($one_line) {
            return '[Exception][action: ' . $this->action
                   . ' | subject: ' . $this->subject
                   . ' | message: ' . $this->message
                   . ' | message_front: ' . $this->message_front
                   . ' | code: ' . $this->code . ']';
        }
        
        return PHP_EOL .
               '[Exception]
[
    action  : ' . $this->action . '
    subject : ' . $this->subject . '
    message : ' . $this->message . '
    message_front : ' . $this->message_front . '
    code    : ' . $this->code . '
]' . PHP_EOL;
    }
    
    
    
    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }
    
    
    
    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }
    
    
    
    /**
     * @return mixed
     */
    public function getMessageFront()
    {
        return $this->message_front;
    }
}
