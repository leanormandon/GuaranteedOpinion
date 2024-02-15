<?php

namespace GuaranteedOpinion\Service;

use GuaranteedOpinion\GuaranteedOpinion;
use GuaranteedOpinion\Model\GuaranteedOpinionProductReview;
use GuaranteedOpinion\Model\GuaranteedOpinionProductReviewQuery;
use Propel\Runtime\Exception\PropelException;

class ProductReviewService
{
    public function addGuaranteedOpinionProductReviews(array $productReviews, int $productId): void
    {
        foreach ($productReviews as $productRow)
        {
            $this->addGuaranteedOpinionProductRow($productRow, $productId);
        }
    }

    public function addGuaranteedOpinionProductRow($row, int $productId): void
    {
        $review = GuaranteedOpinionProductReviewQuery::create()
            ->findOneByProductReviewId($row->id);

        if (null !== $review) {
            return;
        }

        $review = new GuaranteedOpinionProductReview();

        try {
            $review
                ->setProductReviewId($row->id)
                ->setName($row->c)
                ->setReview($row->txt)
                ->setReviewDate($row->date)
                ->setRate($row->r)
                ->setOrderId($row->o)
                ->setOrderDate($row->odate)
                ->setProductId($productId)
            ;

            if ($row->reply !== "" && $row->rdate !== "") {
                $review
                    ->setReply($row->reply)
                    ->setReplyDate($row->rdate)
                ;
            }

            $review->save();

        } catch (PropelException $e) {
            GuaranteedOpinion::log($e->getMessage());
        }
    }

    /**
     * @throws PropelException
     */
    public function deleteReview(int $reviewId): void
    {
        $reviewData = GuaranteedOpinionProductReviewQuery::create()->findOneByProductReviewId($reviewId);

        $reviewData?->delete();
    }

    /**
     * @param string $xml
     * @return array
     */
    public function xmlToArray(string $xml): array
    {
        $result = [];
        $this->normalizeSimpleXML(simplexml_load_string($xml, null, LIBXML_NOCDATA), $result);
        return $result;
    }

    protected function normalizeSimpleXML($obj, &$result): void
    {
        $data = $obj;
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $res = null;
                $this->normalizeSimpleXML($value, $res);
                if (($key === '@attributes') && ($key)) {
                    $result = $res;
                } else {
                    $result[$key] = $res;
                }
            }
        } else {
            $result = $data;
        }
    }
}