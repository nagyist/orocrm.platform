<?php

namespace Oro\Bundle\BusinessEntitiesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;

/**
 * Represents a generic sales order.
 *
 * @package Oro\Bundle\BusinessEntitiesBundle\Entity
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
#[ORM\MappedSuperclass]
class BaseOrder
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BasePerson::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?BasePerson $customer = null;

    /**
     * @var Collection<int, AbstractAddress>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: AbstractAddress::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['primary' => Criteria::DESC])]
    protected ?Collection $addresses = null;

    #[ORM\Column(name: 'currency', type: Types::STRING, length: 10, nullable: true)]
    protected ?string $currency = null;

    #[ORM\Column(name: 'payment_method', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $paymentMethod = null;

    #[ORM\Column(name: 'payment_details', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $paymentDetails = null;

    /**
     * @var double
     */
    #[ORM\Column(name: 'subtotal_amount', type: 'money', nullable: true)]
    protected $subtotalAmount;

    /**
     * @var double
     */
    #[ORM\Column(name: 'shipping_amount', type: 'money', nullable: true)]
    protected $shippingAmount;

    #[ORM\Column(name: 'shipping_method', type: Types::STRING, nullable: true)]
    protected ?string $shippingMethod = null;

    /**
     * @var double
     */
    #[ORM\Column(name: 'tax_amount', type: 'money', nullable: true)]
    protected $taxAmount;

    /**
     * @var double
     */
    #[ORM\Column(name: 'discount_amount', type: 'money', nullable: true)]
    protected $discountAmount;

    /**
     * @var float
     */
    #[ORM\Column(name: 'discount_percent', type: 'percent', nullable: true)]
    protected $discountPercent;

    /**
     * @var double
     */
    #[ORM\Column(name: 'total_amount', type: 'money', nullable: true)]
    protected $totalAmount;

    #[ORM\Column(name: 'status', type: Types::STRING, length: 255, nullable: false)]
    protected ?string $status = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['entity' => ['label' => 'oro.ui.created_at']])]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['entity' => ['label' => 'oro.ui.updated_at']])]
    protected ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, BaseOrderItem>
     */
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: BaseOrderItem::class, cascade: ['all'])]
    protected ?Collection $items = null;

    /**
     * init addresses with empty collection
     */
    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param BasePerson $customer
     *
     * @return $this
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return BasePerson
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set addresses.
     *
     * This method could not be named setAddresses because of bug CRM-253.
     *
     * @param ArrayCollection|AbstractAddress[] $addresses
     *
     * @return $this
     */
    public function resetAddresses($addresses)
    {
        $this->addresses->clear();

        foreach ($addresses as $address) {
            $this->addAddress($address);
        }

        return $this;
    }

    /**
     * Add address
     *
     * @param AbstractAddress $address
     *
     * @return $this
     */
    public function addAddress(AbstractAddress $address)
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
        }

        return $this;
    }

    /**
     * Remove address
     *
     * @param AbstractAddress $address
     *
     * @return $this
     */
    public function removeAddress(AbstractAddress $address)
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
        }

        return $this;
    }

    /**
     * Get addresses
     *
     * @return ArrayCollection|AbstractAddress[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param AbstractAddress $address
     *
     * @return bool
     */
    public function hasAddress(AbstractAddress $address)
    {
        return $this->getAddresses()->contains($address);
    }

    /**
     * @param \DateTime|null $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(?\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(?\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param string $paymentDetails
     *
     * @return $this
     */
    public function setPaymentDetails($paymentDetails)
    {
        $this->paymentDetails = $paymentDetails;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentDetails()
    {
        return $this->paymentDetails;
    }

    /**
     * @param string $paymentMethod
     *
     * @return $this
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param float $discountAmount
     *
     * @return $this
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @param float $discountPercent
     *
     * @return $this
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * @param $shippingAmount
     *
     * @return $this
     */
    public function setShippingAmount($shippingAmount)
    {
        $this->shippingAmount = $shippingAmount;

        return $this;
    }

    /**
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $shippingMethod
     *
     * @return $this
     */
    public function setShippingMethod($shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    /**
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->shippingMethod;
    }

    /**
     * @return float
     */
    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param float $subtotalAmount
     *
     * @return $this
     */
    public function setSubtotalAmount($subtotalAmount)
    {
        $this->subtotalAmount = $subtotalAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getSubtotalAmount()
    {
        return $this->subtotalAmount;
    }

    /**
     * @param float $taxAmount
     *
     * @return $this
     */
    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * @param float $totalAmount
     *
     * @return $this
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param BaseOrderItem[] $items
     *
     * @return $this
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return BaseOrderItem[]|ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param BaseOrderItem $item
     *
     * @return $this
     */
    public function addItem(BaseOrderItem $item)
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    /**
     * @param BaseOrderItem $item
     *
     * @return $this
     */
    public function removeItem(BaseOrderItem $item)
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
        }

        return $this;
    }

    /**
     * Clone relations
     */
    public function __clone()
    {
        if ($this->addresses) {
            $this->addresses = clone $this->addresses;
        }

        if ($this->items) {
            $this->items = clone $this->items;
        }
    }

    /**
     * @return string
     */
    #[\Override]
    public function __toString()
    {
        return (string)$this->getId();
    }
}
