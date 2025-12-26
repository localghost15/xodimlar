<?php

namespace App\Service;

use App\Entity\Asset;
use App\Entity\PurchaseRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AssetService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {
    }

    public function createAssetFromPurchase(PurchaseRequest $purchase, User $creator, string $inventoryNumber, string $serialNumber): Asset
    {
        $asset = new Asset();
        $asset->setPurchaseRequest($purchase);
        $asset->setCurrentOwner($purchase->getUser()); // Initially assigned to the requester
        $asset->setInventoryNumber($inventoryNumber);
        $asset->setSerialNumber($serialNumber);
        $asset->setStatus('active');

        // Generate QR Slug: inventory-serial-random
        $qrSlug = sprintf(
            '%s-%s-%s',
            $this->slugger->slug($inventoryNumber),
            $this->slugger->slug($serialNumber),
            uniqid()
        );
        $asset->setQrCode($qrSlug);

        // Update purchase status
        $purchase->setStatus('asset_created');

        $this->entityManager->persist($asset);
        $this->entityManager->flush();

        return $asset;
    }
}
