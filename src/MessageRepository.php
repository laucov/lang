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
class MessageRepository
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
     * 
     * @var array<string>
     */
    protected array $accepted = [];

    /**
     * Language data.
     * 
     * @var array<string, ArrayBuilder>
     */
    protected array $data = [];

    /**
     * Language file directories.
     * 
     * @var array<string>
     */
    protected array $directories = [];

    /**
     * Language redirects.
     * 
     * @var array<string, string>
     */
    protected array $redirects = [];

    /**
     * Supported languages.
     * 
     * @var array<string>
     */
    protected array $supported = [];

    /**
     * Set a language data source directory.
     * 
     * PHP files in the directory will be used to get language data on demand.
     */
    public function addDirectory(string $path): static
    {
        $this->directories[] = rtrim($path, '\\/');
        return $this;
    }

    /**
     * Find and format a message.
     */
    public function findMessage(string $path, array $args = []): null|string
    {
        // Split path.
        $segments = explode('.', $path);

        // Get message from accepted languages.
        $message = null;
        foreach ($this->accepted as $tag) {
            // Check if is supported.
            if (!in_array($tag, $this->supported, true)) {
                continue;
            }
            // Replace redirected tags.
            while (isset($this->redirects[$tag])) {
                $tag = $this->redirects[$tag];
            }
            // Check if exists and try to load data.
            if (!isset($this->data[$tag]) && !$this->loadLanguageData($tag)) {
                continue;
            }
            // Get message.
            $message = $this->data[$tag]->getValue($segments);
            if ($message !== null || !$this->fallback) {
                break;
            }
        }

        // Check default language.
        if ($message === null && $this->defaultLanguage !== null) {
            $tag = $this->defaultLanguage;
            if (isset($this->data[$tag]) || $this->loadLanguageData($tag)) {
                $message = $this->data[$tag]->getValue($segments);
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
    public function setAcceptedLanguages(string ...$list): static
    {
        $this->accepted = $list;
        return $this;
    }

    /**
     * Set messages for the given language.
     */
    public function setLanguageData(string $tag, array $data): static
    {
        $this->data[$tag] = new ArrayBuilder($data);
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

    /**
     * Redirect a locale to another.
     * 
     * Useful for redirecting locales with no region to their default ones.
     */
    public function redirect(string $from_tag, string $to_tag): static
    {
        $this->redirects[$from_tag] = $to_tag;
        return $this;
    }

    /**
     * Search directories and try to load language data for the given locale.
     */
    protected function loadLanguageData(string $locale): bool
    {
        // Check each registered directory.
        foreach ($this->directories as $directory) {
            // Build filename.
            $filename = $directory . DIRECTORY_SEPARATOR . $locale . '.php';
            // Check if exists and require the script.
            if (file_exists($filename)) {
                $this->setLanguageData($locale, require $filename);
                return true;
            }
        }

        return false;
    }
}
