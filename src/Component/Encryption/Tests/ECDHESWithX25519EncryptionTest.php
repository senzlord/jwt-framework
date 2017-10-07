<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2017 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Jose\Component\Encryption\Tests;

use Jose\Component\KeyManagement\JWKFactory;

/**
 * final class ECDHESWithX25519EncryptionTest.
 *
 * @group ECDHES
 * @group Unit
 */
final class ECDHESWithX25519EncryptionTest extends AbstractEncryptionTest
{
    /**
     * @see https://tools.ietf.org/html/rfc7516#appendix-B
     */
    public function testA128CBCHS256EncryptAndDecrypt()
    {
        $receiverKey = JWKFactory::createOKPKey('X25519');
        $input = "You can trust us to stick with you through thick and thin\xe2\x80\x93to the bitter end. And you can trust us to keep any secret of yours\xe2\x80\x93closer than you keep it yourself. But you cannot trust us to let you face trouble alone, and go off without a word. We are your friends, Frodo.";

        $protectedHeaders = [
            'alg' => 'ECDH-ES+A128KW',
            'enc' => 'A128GCM',
        ];

        $jweBuilder = $this->getJWEBuilderFactory()->create(['ECDH-ES+A128KW'], ['A128GCM'], ['DEF']);
        $jweLoader = $this->getJWELoaderFactory()->create(['ECDH-ES+A128KW'], ['A128GCM'], ['DEF'], [], ['jwe_compact', 'jwe_json_flattened', 'jwe_json_general']);

        $jwt = $jweBuilder
            ->create()->withPayload($input)
            ->withSharedProtectedHeaders($protectedHeaders)
            ->addRecipient($receiverKey)
            ->build();
        $jwt = $this->getJWESerializerManager()->serialize('jwe_compact', $jwt, 0);

        $jwe = $jweLoader->load($jwt);
        $jwe = $jweLoader->decryptUsingKey($jwe, $receiverKey, $index);
        self::assertEquals(0, $index);
        self::assertTrue($jwe->hasSharedProtectedHeader('epk'));
        self::assertEquals($input, $jwe->getPayload());
    }
}