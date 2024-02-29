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

use Laucov\Lang\Language;
use Laucov\Lang\LanguageList;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Laucov\Lang\LanguageList
 */
class LanguageListTest extends TestCase
{
    /**
     * @covers ::fromHeader
     * @uses Laucov\Lang\Language::__construct
     * @uses Laucov\Lang\LanguageList::add
     * @uses Laucov\Lang\LanguageList::compare
     * @uses Laucov\Lang\LanguageList::get
     */
    public function testCanCreateFromLanguageHeader(): void
    {
        // Create list.
        $header = '*;q=0.1, de-DE-1996, pt-AO;q=0.5';
        $list = LanguageList::fromHeader($header);

        // Check return type.
        $this->assertInstanceOf(LanguageList::class, $list);

        // Check languages.
        $expected = ['de-DE-1996', 'pt-AO', '*', null, null];
        foreach ($expected as $i => $tag) {
            $lang = $list->get($i);
            if ($tag === null) {
                $this->assertNull($lang);
                continue;
            }
            $this->assertInstanceOf(Language::class, $lang);
            $this->assertSame($tag, $lang->tag);
        }
    }

    /**
     * @covers ::fromHeader
     * @uses Laucov\Lang\Language::__construct
     * @uses Laucov\Lang\LanguageList::add
     * @uses Laucov\Lang\LanguageList::compare
     * @uses Laucov\Lang\LanguageList::get
     */
    public function testMustPassValidLanguageHeader(): void
    {
        // Test invalid yet syntactically correct values.
        LanguageList::fromHeader('FOOBAR-BAZ-123, MYLANG;q=489.124874421');

        // Test invalid synthax.
        $this->expectException(\InvalidArgumentException::class);
        LanguageList::fromHeader('pt-BR, pt-AO;q=FOO');
    }

    /**
     * @covers ::add
     * @covers ::compare
     * @covers ::get
     * @covers ::getTags
     * @uses Laucov\Lang\Language::__construct
     */
    public function testSortsLanguages(): void
    {
        // Add languages.
        $list = new LanguageList();
        $list
            ->add('pt-BR', 0.9)
            ->add('es-MX', 0.3)
            ->add('en-US', 0.5);
        
        // Get languages by preference.
        $expected = ['pt-BR', 'en-US', 'es-MX', null, null];
        foreach ($expected as $i => $tag) {
            $lang = $list->get($i);
            if ($tag === null) {
                $this->assertNull($lang);
                continue;
            }
            $this->assertInstanceOf(Language::class, $lang);
            $this->assertSame($tag, $lang->tag);
        }

        // Get language tags.
        $tags = $list->getTags();
        $this->assertIsArray($tags);
        $this->assertCount(3, $tags);
        $this->assertSame('pt-BR', $tags[0]);
        $this->assertSame('en-US', $tags[1]);
        $this->assertSame('es-MX', $tags[2]);
    }
}
