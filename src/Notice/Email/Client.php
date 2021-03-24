<?php

namespace Gather\Notice\Email;

use Gather\Kernel\BaseClient;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;

/**
 * Class Client
 * auther: jtar <3196672779@qq.com>
 * Time: 2020/11/2 12:29
 * @package Gather\Notice\Email
 */
class Client extends BaseClient
{
    /**
     * The program run error send email.
     *
     * @package See:https://packagist.org/packages/phpmailer/phpmailer
     *
     * @param string $address
     * @param string $title
     * @param string $content
     *
     * @return bool
     *
     * @throws \Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function to($address = '',$title = 'Error',$content = '')
    {
        $config = $this->app['config']['email'];

        if ($this->check($config) != true){
            return  false;
        }

        try {
            $mail = new PHPMailer(true);

            if (true == $config['SMTPDebug']){
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            
            $mail->isSMTP();

            $mail->CharSet    = 'UTF-8';
            $mail->Host       = $config['Host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['Username'];
            $mail->Password   = $config['Password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $config['Port'];

            $mail->setFrom($config['Username']);
            $mail->addAddress($address);

            $mail->isHTML(true);

            $mail->Subject = $title;
            $mail->Body    = $this->content($config,$content);

            return $mail->send();

        } catch (\PHPMailer\PHPMailer\Exception $e) {

            $log = new Logger('name');
            $log->pushHandler(new StreamHandler($config['ErrorLog'], Logger::ERROR));
            $log->error("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    /**
     * get send email content.
     * @param $config
     * @param $content
     * @return string
     */
    protected function content($config,$content)
    {
        if ($config['IsEx']){
            $content = (new HtmlErrorRenderer(true))->render($content)->getAsString();
        }

        return $content;
    }

    /**
     * Send before check.
     *
     * @param $config
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function check($config)
    {
        if ($config['IsInterval'] == false){
            return true;
        }

        $cachePool = new FilesystemAdapter($config['IntervalName'], 0, 'cache');
        $get = $cachePool->getItem($config['IntervalName']);

        if (!$get->isHit())
        {
            $get->set($config['IntervalName']);
            $get->expiresAfter($config['IntervalTime'] * 60 );
            $cachePool->save($get);

            return  true;
        }

        return  false;
    }
}
