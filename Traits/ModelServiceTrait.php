<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits;

use Velocity\Bundle\ApiBundle\RepositoryInterface;

/**
 * ModelManager trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait ModelServiceTrait
{
    use ModelServiceHelperTrait;
    /**
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->getService('repository');
    }
    /**
     * @param RepositoryInterface $repository
     *
     * @return $this
     */
    public function setRepository(RepositoryInterface $repository)
    {
        return $this->setService('repository', $repository);
    }
    /**
     * @param array $ids
     * @param array $options
     *
     * @return string
     */
    public function getRepoKey(array $ids = [], $options = [])
    {
        $options += ['pattern' => '%ss', 'skip' => 0, 'separator' => '.'];

        $key    = '';
        $types  = $this->getTypes();
        $toSkip = $options['skip'];
        $sep    = $options['separator'];

        array_shift($types);

        while (count($types)) {
            $type = array_shift($types);
            if (!$toSkip) {
                $key .= ($key ? $sep : '').sprintf($options['pattern'], $type);
            } else {
                $toSkip--;
                if ($toSkip) {
                    continue;
                }
            }

            if (!count($ids)) {
                if (!count($types)) {
                    break;
                }
                $id = 'unknown';
            } else {
                $id = array_shift($ids);
            }

            $this->checkRepoKeyTokenIsValid($id, $sep);

            $key .= ($key ? $sep : '').$id;
        }

        if (count($ids)) {
            foreach ($ids as $id) {
                $this->checkRepoKeyTokenIsValid($id, $sep);
                $key .= ($key ? $sep : '').$id;
            }
        }

        return $key;
    }
    /**
     * @param array    $items
     * @param array    $criteria
     * @param array    $fields
     * @param \Closure $eachCallback
     * @param array    $options
     *
     * @return $this
     */
    protected function filterItems(&$items, $criteria = [], $fields = [], \Closure $eachCallback = null, $options = [])
    {
        if (!is_array($fields)) {
            $fields = [];
        }
        if (!is_array($criteria)) {
            $criteria = [];
        }

        if (empty($items)) {
            return $this;
        }

        $keyFields     = array_fill_keys($fields, true);
        $fieldFiltered = false;

        if (is_array($criteria) && count($criteria) > 0) {
            $fieldFiltered = true;
            foreach ($criteria as $criteriaKey => $criteriaValue) {
                if (false !== strpos($criteriaKey, ':')) {
                    list($criteriaKey, $criteriaValueType) = explode(':', $criteriaKey, 2);
                    switch (trim($criteriaValueType)) {
                        case 'int':
                            $criteriaValue = (int) $criteriaValue;
                            break;
                        case 'string':
                            $criteriaValue = (string) $criteriaValue;
                            break;
                        case 'bool':
                            $criteriaValue = (bool) $criteriaValue;
                            break;
                        case 'array':
                            $criteriaValue = json_decode($criteriaValue, true);
                            break;
                        case 'float':
                            $criteriaValue = (double) $criteriaValue;
                            break;
                        default:
                            break;
                    }
                }
                foreach ($items as $id => $item) {
                    if ('*empty*' === $criteriaValue) {
                        if (isset($item[$criteriaValue]) && strlen($item[$criteriaValue])) {
                            unset($items[$id]);
                            continue;
                        }
                        continue;
                    } elseif ('*notempty*' === $criteriaValue) {
                        if (!isset($item[$criteriaValue]) || !strlen($item[$criteriaValue])) {
                            unset($items[$id]);
                            continue;
                        }
                        continue;
                    } elseif ('$or' === $criteriaKey) {
                        foreach ($criteriaValue as $cv) {
                            foreach ($cv as $cc => $vv) {
                                if (isset($item[$cc]) && $item[$cc] === $vv) {
                                    continue 3;
                                }
                            }
                        }
                        unset($items[$id]);
                    }
                    if (!isset($item[$criteriaKey]) || ($item[$criteriaKey] !== $criteriaValue)) {
                        unset($items[$id]);
                        continue;
                    }
                    if ($eachCallback) {
                        $item = $eachCallback($item);
                    }
                    if (is_array($fields) && count($fields) > 0) {
                        $item = array_intersect_key($item, $keyFields);
                        $items[$id] = $item;
                    }
                }
            }
        }

        if (!$fieldFiltered) {
            foreach ($items as $id => $item) {
                if ($eachCallback) {
                    $item = $eachCallback($item);
                }
                if (is_array($fields) && count($fields) > 0) {
                    $item = array_intersect_key($item, $keyFields);
                    $items[$id] = $item;
                }
            }
        }

        unset($options);

        return $this;
    }
    /**
     * @param array $items
     * @param int   $limit
     * @param int   $offset
     * @param array $options
     *
     * @return $this
     */
    protected function paginateItems(&$items, $limit, $offset, $options = [])
    {
        if (empty($items)) {
            return $this;
        }

        if (is_numeric($offset) && $offset > 0) {
            if (is_numeric($limit) && $limit > 0) {
                $items = array_slice($items, $offset, $limit, true);
            } else {
                $items = array_slice($items, $offset, null, true);
            }
        } else {
            if (is_numeric($limit) && $limit > 0) {
                $items = array_slice($items, 0, $limit, true);
            }
        }

        unset($options);

        return $this;
    }
    /**
     * @param array $items
     * @param array $sorts
     * @param array $options
     *
     * @return $this
     */
    protected function sortItems(&$items, $sorts = [], $options = [])
    {
        if (empty($items)) {
            return $this;
        }

        if (!is_array($sorts)) {
            $sorts = [];
        }

        uasort($items, function ($a, $b) use ($sorts) {
            foreach ($sorts as $field => $direction) {
                if (false === $direction || -1 === (int) $direction || 0 === (int) $direction || 'false' === $direction || null === $direction) {
                    if (!isset($a[$field])) {
                        if (!isset($b[$field])) {
                            continue;
                        } else {
                            return -1;
                        }
                    } elseif (!isset($b[$field])) {
                        continue;
                    }
                    $result = strcmp($b[$field], $a[$field]);

                    if ($result > 0) {
                        return $result;
                    }
                } else {
                    if (!isset($a[$field])) {
                        if (!isset($b[$field])) {
                            continue;
                        } else {
                            return 1;
                        }
                    } elseif (!isset($b[$field])) {
                        continue;
                    }
                    $result = strcmp($a[$field], $b[$field]);

                    if ($result > 0) {
                        return $result;
                    }
                }
            }

            return -1;
        });

        unset($options);

        return $this;
    }
    /**
     * @param array $array
     * @param array $ids
     * @param array $options
     *
     * @return array
     */
    protected function mutateArrayToRepoChanges($array, array $ids = [], $options = [])
    {
        $changes  = [];

        foreach ($array as $k => $v) {
            $changes[$this->mutateKeyToRepoChangesKey($k, $ids)] = $v;
        }

        unset($options);

        return $changes;
    }

    /**
     * @param string $key
     * @param array  $ids
     * @param array  $options
     *
     * @return string
     */
    protected function mutateKeyToRepoChangesKey($key, array $ids = [], array $options = [])
    {
        unset($options);

        return sprintf('%s.%s', $this->getRepoKey($ids), $key);
    }
    /**
     * @param string $token
     * @param string $sep
     *
     * @return $this
     *
     * @throws \Exception
     */
    protected function checkRepoKeyTokenIsValid($token, $sep)
    {
        if (false !== strpos($token, $sep)) {
            throw $this->createMalformedException("Key token '%s' is invalid (found: %s)", $token, $sep);
        }

        if (0 === strlen($token)) {
            throw $this->createMalformedException('Key token is empty', $token, $sep);
        }

        return $this;
    }
}
