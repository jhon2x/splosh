<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search
 * @version   1.0.124
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Service;

use Mirasvit\Search\Api\Service\CloudServiceInterface;

class CloudService implements CloudServiceInterface
{
    const ENDPOINT = 'http://mirasvit.com/media/cloud/';

    /**
     * {@inheritdoc}
     */
    public function getList($module, $entity)
    {
        $list = $this->request($module, $entity, 'list');

        $result = [];
        if ($list) {
            foreach ($list as $item) {
                $result[] = [
                    'value' => $item['identifier'],
                    'label' => $item['name'],
                ];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function get($module, $entity, $identifier)
    {
        return $this->request($module, $entity, 'get', ['identifier' => $identifier]);
    }

    /**
     * @param string $module
     * @param string $entity
     * @param string $action
     * @param array  $optional
     * @return false|string
     */
    private function request($module, $entity, $action, $optional = [])
    {
        $args = [
            'module' => $module,
            'entity' => $entity,
            'action' => $action,
        ];

        $args = array_merge_recursive($args, $optional);

        $query = http_build_query($args);

        try {
            $result = json_decode(file_get_contents(self::ENDPOINT . '?' . $query), true);

            if ($result['success']) {
                return $result['data'];
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
