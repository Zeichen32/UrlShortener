<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 16.04.2015
 * Time: 14:15
 */

namespace TwoDevs\UrlShortener\Provider;

use Symfony\Component\OptionsResolver\OptionsResolver;
use TwoDevs\UrlShortener\Utils\UrlInterface;

class VgdProvider extends IsgdProvider
{
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->setDefaults([
            'endpoint' => 'http://v.gd/',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function canExpand(UrlInterface $url)
    {
        return ('v.gd' == $url->getHost());
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'vgd';
    }
}
