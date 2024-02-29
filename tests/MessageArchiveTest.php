<?php

/**
 * This file is part of Laucov's Language Library project.
 * 
 * Copyright 2024 Laucov Serviços de Tecnologia da Informação Ltda.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @package lang
 * 
 * @author Rafael Covaleski Pereira <rafael.covaleski@laucov.com>
 * 
 * @license <http://www.apache.org/licenses/LICENSE-2.0> Apache License 2.0
 * 
 * @copyright © 2024 Laucov Serviços de Tecnologia da Informação Ltda.
 */

declare(strict_types=1);

namespace Tests;

use Laucov\Lang\LanguageList;
use Laucov\Lang\MessageArchive;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Lang\MessageArchive
 */
class MessageArchiveTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::findMessage
     * @covers ::setAcceptedLanguages
     * @covers ::setLanguageData
     * @covers ::setSupportedLanguages
     * @uses Laucov\Lang\Language::__construct
     * @uses Laucov\Lang\LanguageList::add
     * @uses Laucov\Lang\LanguageList::compare
     * @uses Laucov\Lang\LanguageList::fromHeader
     * @uses Laucov\Lang\LanguageList::get
     */
    public function testCanAddLanguagesAndFindMessages(): void
    {
        // Create archive.
        $archive = new MessageArchive();

        // Create a list of accepted languages.
        $list = LanguageList::fromHeader('pt-PT;q=0.5, pt-BR;q=0.9');

        // Configure.
        $archive->defaultLanguage = 'en-US';
        $archive
            ->setAcceptedLanguages($list)
            ->setSupportedLanguages('pt-PT', 'pt-BR', 'en')
            ->setLanguageData('en-US', [
                'fox' => 'The quick brown fox jumps over the lazy dog.',
                'hello' => [
                    'world' => 'Hello, World!',
                    'universe' => 'Hello, Universe!',
                    'everyone' => 'Hello, Everyone!',
                ],
                'count' => '{0,number,integer} files found',
            ])
            ->setLanguageData('pt-BR', [
                'today' => 'Hoje é {date, date, full}.',
                'fox' => 'A raposa marrom e ligeira pula sobre o cachorro preguiçoso.',
                'hello' => [
                    'world' => 'Olá, Mundo!',
                ],
                'count' => '{0,number,integer} arquivos encontrados',
            ])
            ->setLanguageData('pt-PT', [
                'fox' => 'A rápida raposa marrom salta sobre o cão preguiçoso.',
                'hello' => [
                    'universe' => 'Olá, Universo!',
                ],
                'count' => '{0,number,integer} ficheiros encontrados',
            ]);

        // Get a message.
        $message_a = $archive->findMessage('fox');
        $this->assertSame(
            'A raposa marrom e ligeira pula sobre o cachorro preguiçoso.',
            $message_a,
        );

        // Test nesting.
        $message_b = $archive->findMessage('hello.world');
        $this->assertSame('Olá, Mundo!', $message_b);

        // Test fallback to other accepted languages.
        $message_c = $archive->findMessage('hello.universe');
        $this->assertSame('Olá, Universo!', $message_c);

        // Test fallback to the default language.
        $message_d = $archive->findMessage('hello.everyone');
        $this->assertSame('Hello, Everyone!', $message_d);
        $archive->defaultLanguage = null;
        $this->assertNull($archive->findMessage('hello.everyone'));

        // Test without fallback.
        $archive->fallback = false;
        $archive->defaultLanguage = 'en-US';
        $message_e = $archive->findMessage('hello.universe');
        $this->assertSame('Hello, Universe!', $message_e);
        $archive->defaultLanguage = null;
        $this->assertNull($archive->findMessage('hello.universe'));

        // Test formatting.
        $message_f = $archive->findMessage('today', ['date' => 0]);
        $this->assertSame(
            'Hoje é quinta-feira, 1 de janeiro de 1970.',
            $message_f,
        );

        // Limit supported languages.
        $message_g = $archive
            ->setSupportedLanguages('pt-PT', 'es-MX')
            ->findMessage('count', [2141]);
        $this->assertSame("2\u{00A0}141 ficheiros encontrados", $message_g);
        $message_h = $archive
            ->setSupportedLanguages('es-MX')
            ->findMessage('count', [2141]);
        $this->assertNull($message_h);

        // Always support the default language.
        $archive->defaultLanguage = 'en-US';
        $message_i = $archive->findMessage('count', [2141]);
        $this->assertSame("2,141 files found", $message_i);
    }
}
