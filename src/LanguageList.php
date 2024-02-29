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

namespace Laucov\Lang;

/**
 * Stores and sorts language objects.
 */
class LanguageList
{
    const LANG_PATT = '/^(\*|([^\W_]+(\-[^\W_]+)*))(;q=(\d+(\.\d+)?)?)?$/';

    /**
     * Create a list from an HTTP "Accept-Language" header.
     */
    public static function fromHeader(string $header): LanguageList
    {
        $list = new LanguageList();
        
        $values = array_map('trim', explode(',', $header));
        foreach ($values as $value) {
            if (preg_match(static::LANG_PATT, $value) !== 1) {
                $message = 'Invalid "Accept-Language" value "' . $value . '".';
                throw new \InvalidArgumentException($message);
            }
            $dirs = array_map('trim', explode(';', $value));
            $weight = isset($dirs[1]) ? floatval(substr($dirs[1], 2)) : 1.0;
            $list->add($dirs[0], $weight);
        }

        return $list;
    }

    /**
     * Added languages.
     * 
     * @var array<Language>
     */
    protected array $languages = [];

    /**
     * Add a language to the list.
     */
    public function add(string $tag, float $weight): static
    {
        $this->languages[] = new Language($tag, $weight);
        usort($this->languages, [$this, 'compare']);
        return $this;
    }

    /**
     * Get a language by preference position.
     * 
     * Languages are sorted by their q-factor weight.
     */
    public function get(int $position): null|Language
    {
        return $this->languages[$position] ?? null;
    }

    /**
     * Compare two languages' weights.
     */
    public function compare(Language $a, Language $b): int
    {
        return match (true) {
            $a->weight < $b->weight => 1,
            $a->weight === $b->weight => 0,
            $a->weight > $b->weight => -1,
        };
    }
}
