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

use Laucov\Arrays\ArrayBuilder;

/**
 * Stores and retrieves multi-language messages.
 */
class MessageArchive
{
    /**
     * Default language.
     */
    public null|string $defaultLanguage = null;

    /**
     * Whether to use all accepted languages to find a message.
     */
    public bool $fallback = true;

    /**
     * Accepted languages.
     */
    protected LanguageList $accepted;

    /**
     * Language data.
     */
    protected ArrayBuilder $data;

    /**
     * Supported languages.
     * 
     * @var array<string>
     */
    protected array $supported = [];

    /**
     * Create the message archive instance.
     */
    public function __construct()
    {
        $this->data = new ArrayBuilder([]);
    }

    /**
     * Find and format a message.
     */
    public function findMessage(string $path, array $args = []): null|string
    {
        // Split path.
        $segments = explode('.', $path);

        // Search into each accepted language.
        $message = null;
        $i = 0;
        while ($lang = $this->accepted->get($i)) {
            $tag = $lang->tag;
            if (!in_array($tag, $this->supported, true)) {
                $i++;
                continue;
            }
            $keys = [$tag, ...$segments];
            $value = $this->data->getValue($keys, null);
            if ($value !== null) {
                $message = $value;
                break;
            }
            if ($this->fallback) {
                $i++;
            } else {
                break;
            }
        }

        // Check default language.
        if ($message === null && $this->defaultLanguage !== null) {
            $tag = $this->defaultLanguage;
            $keys = [$this->defaultLanguage, ...$segments];
            $value = $this->data->getValue($keys, null);
            if ($value !== null) {
                $message = $value;
            }
        }

        // Format.
        if ($message !== null && count($args) > 0) {
            $message = msgfmt_format_message($tag, $message, $args);
        }

        return $message;
    }

    /**
     * Set accepted languages.
     */
    public function setAcceptedLanguages(LanguageList $list): static
    {
        $this->accepted = $list;
        return $this;
    }

    /**
     * Set messages for the given language.
     */
    public function setLanguageData(string $tag, array $data): static
    {
        $this->data->setValue($tag, $data);
        return $this;
    }

    /**
     * Set supported language tags.
     */
    public function setSupportedLanguages(string ...$tags): static
    {
        $this->supported = $tags;
        return $this;
    }
}
