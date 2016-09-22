<?php declare(strict_types = 1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.09.16 at 09:36
 */

require __DIR__ . '/vendor/autoload.php';
require 'BitBucketCloudReporter.php';

new \samsonframework\bitbucket\cloud\BitBucketCloudReporter(
    new \Bitbucket\API\Authentication\Basic('info@samsonos.com', 'Vovan2912~'),
    'samsonos',
    'omnivalor',
    1019
);