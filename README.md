#php7-request-query
```PHP
<?php
/**
 * Created by PhpStorm.
 * User: arturmich
 * Date: 3/5/19
 * Time: 11:00 AM
 */

namespace Lemons24\Scizzy\Models\StoreWithPartner;


use Audi2014\RequestQuery\AbstractRequestQueryPage;

class StoreWithPartnerQueryPage extends AbstractRequestQueryPage {
    protected $or = false;

    public function setOrForConditions(bool $or) {
        $this->or = $or;
        return $this;
    }

//eq
    public $isApproved = 1;
    public $id;
    public $partnerId;
    public $discountType;
//in
    public $partnerIds;
    public $ids;
//>= <=
    public $latitude_min;
    public $latitude_max;
    public $longitude_min;
    public $longitude_max;
    public $discountValue_min;
    public $discountValue_max;
    public $discountStart_after;
    public $discountStart_before;
    public $discountEnd_before;
    public $discountEnd_after;
    public $updatedAt_after;
    public $updatedAt_before;
    public $createdAt_after;
    public $createdAt_before;
//like
    public $companyName;
    public $discountName;
    public $fullName;
    public $title;
    public $description;
    public $address;
    public $phone;
    public $email;

    protected function getConditionGroup(string $key,
                                         string &$groupId,
                                         bool &$orForConditions,
                                         bool &$orForGroup,
                                         bool &$isHaving,
                                         bool &$orNullConditionMode): void {
        if ($key === 'isApproved') {
            $groupId = 'isApproved';
            $orForConditions = false;
            $orForGroup = false;
        } else {
            $orForConditions = $this->or;
        }
        if (in_array($key, [
            'latitude_min',
            'latitude_max',
            'longitude_min',
            'longitude_max',
        ])) {
            $groupId = 'location';
            $orForConditions = false;
            $orForGroup = false;
            $orNullConditionMode = true;
        }
    }

    protected function getEqKeys(): array {
        return [
            'isApproved' => 'partner.isApproved',
            'id' => 'store.id',
            'partnerId' => 'store.partnerId',
            'discountType' => 'partner.discountType',
        ];
    }

    protected function getInKeys(): array {
        return [
            'ids' => 'store.id',
            'partnerIds' => 'store.partnerId',
        ];
    }

    protected function getGthEqKeys(): array {
        return [
            'longitude_min' => 'store.longitude',
            'latitude_min' => 'store.latitude',
            'discountValue_min' => 'partner.discountValue',

            'updatedAt_after' => 'store.updatedAt',
            'createdAt_after' => 'store.createdAt',
            'discountStart_after' => 'partner.discountStart',
            'discountEnd_after' => 'partner.discountEnd',
        ];
    }

    protected function getLthEqKeys(): array {
        return [
            'longitude_max' => 'store.longitude',
            'latitude_max' => 'store.latitude',
            'discountValue_max' => 'partner.discountValue',

            'updatedAt_before' => 'store.updatedAt',
            'createdAt_before' => 'store.createdAt',
            'discountStart_before' => 'partner.discountStart',
            'discountEnd_before' => 'partner.discountEnd',
        ];
    }

    protected function getLikeKeys(): array {
        return [
//            'categoryName' => 'category.name',

            'companyName' => 'partner.companyName',
            'discountName' => 'partner.discountName',
            'fullName' => 'partner.fullName',

            'title' => 'store.title',
            'description' => 'store.description',
            'address' => 'store.address',
            'phone' => 'store.phone',
            'email' => 'store.email',

        ];
    }

    protected function getOrderByKeys(): array {
        return [
            'title' => 'store.title',
            'id' => 'store.id',
            'partnerId' => 'store.partnerId',
            'updatedAt' => 'store.updatedAt',
            'createdAt' => 'store.createdAt',
        ];
    }
}
```