<?php
/*
 * This file is part of the fnacApi.
 * (c) 2011 Fnac
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FnacApiClient\Service\Response;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use FnacApiClient\Entity\ClientOrderComment;

/**
 * ClientOrderCommentQueryResponse service base definition for client order comment query response
 *
 * @author     Fnac
 * @version    1.0.0
 */
class ClientOrderCommentQueryResponse extends QueryResponse
{
    private $client_order_comments = array();

    /**
     * {@inheritdoc}
     */
    public function denormalize(NormalizerInterface $normalizer, $data, $format = null)
    {
        parent::denormalize($normalizer, $data, $format);

        $this->client_order_comments = new \ArrayObject();

        if (isset($data['client_order_comment'])) {
            if (isset($data['client_order_comment'][0])) {
                foreach ($data['client_order_comment'] as $client_order_comment) {
                    $tmpObj = new ClientOrderComment();
                    $tmpObj->denormalize($normalizer, $client_order_comment, $format);
                    $this->client_order_comments[] = $tmpObj;
                }
            } else {
                $tmpObj = new ClientOrderComment();
                $tmpObj->denormalize($normalizer, $data['client_order_comment'], $format);
                $this->client_order_comments[] = $tmpObj;
            }
        }
    }

    /**
     * Client order comment list
     *
     * @see FnacApiClient\Entity\ClientOrderComment;
     *
     * @return ArrayObject<ClientOrderComment>
     */
    public function getClientOrderComments()
    {
        return $this->client_order_comments;
    }
}
