<?php

declare(strict_types=1);

enum NotificationType: string
{
    case CRITICAL_STOCK = 'CRITICAL_STOCK';
    case OUT_OF_STOCK = 'OUT_OF_STOCK';
    case LOW_STOCK = 'LOW_STOCK';
}
readonly class Product
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $stock,
        public readonly int $criticalStock
    ) {}
}

readonly class StockNotification
{
    public function __construct(
        public readonly Product $product,
        public readonly NotificationType $type,
        public readonly DateTime $timestamp
    ) {}

    public function getMessage(): string
    {
        return match ($this->type) {
            NotificationType::CRITICAL_STOCK => sprintf(
                'Product "%s" has reached critical stock level (%d/%d)',
                $this->product->name,
                $this->product->stock,
                $this->product->criticalStock
            ),
            NotificationType::LOW_STOCK => sprintf(
                'Product "%d" is running low on stock (%d remaining)',
                $this->product->name,
                $this->product->stock
            ),
            NotificationType::OUT_OF_STOCK => sprintf(
                'Product "%s" is out of stock',
                $this->product->name
            )
        };
    }
}
interface InventoryTrackerInterface
{
    public function processStockUpdate(Product $product): void;
    public function addNotifier(CriticalStockNotifierInterface $notifier): void;
    public function removeNotifier(CriticalStockNotifierInterface $notifier): void;
}

interface CriticalStockNotifierInterface
{
    public function notify(StockNotification $message): void;
}

class InventoryTracker implements InventoryTrackerInterface
{
    private array $notifiers = [];
    public function processStockUpdate(Product $product): void
    {
        if ($product->stock <= 0) {
            $this->notifyAll(
                new StockNotification(
                    product: $product,
                    type: NotificationType::OUT_OF_STOCK,
                    timestamp: new DateTime()
                )
            );
        } elseif ($product->stock < $product->criticalStock) {
            $this->notifyAll(
                new StockNotification(
                    product: $product,
                    type: NotificationType::CRITICAL_STOCK,
                    timestamp: new DateTime()
                )
            );
        }
    }

    private function notifyAll(StockNotification $notification): void
    {
        foreach ($this->notifiers as $notifier) {
            $notifier->notify($notification);
        }
    }

    public function addNotifier(CriticalStockNotifierInterface $notifier): void
    {
        $this->notifiers[spl_object_hash($notifier)] = $notifier;
    }

    public function removeNotifier(CriticalStockNotifierInterface $notifier): void
    {
        unset($this->notifiers[spl_object_hash($notifier)]);
    }
}

class PurchasingDepartmentNotifier implements CriticalStockNotifierInterface
{
    public function notify(StockNotification $message): void
    {
        echo "Notification for Purchasing Department: {$message->getMessage()} <br>";
    }
}

class SupplierEmailNotifier implements CriticalStockNotifierInterface
{
    public function notify(StockNotification $message): void
    {
        echo "Notification for Supplier: {$message->getMessage()} <br>";
    }
}

class WebsiteUpdater implements CriticalStockNotifierInterface
{
    public function notify(StockNotification $message): void
    {
        echo "Notification for Website: {$message->getMessage()} <br>";
    }
}

class ManagerSmsNotifier implements CriticalStockNotifierInterface
{
    public function notify(StockNotification $message): void
    {
        echo "Notification for Manager: {$message->getMessage()} <br>";
    }
}
$subscribers = list($departmentNotifier, $supplierNotifier, $websiteUpdater, $managerNotifier) = [
    new PurchasingDepartmentNotifier(),
    new SupplierEmailNotifier(),
    new WebsiteUpdater(),
    new ManagerSmsNotifier()
];
for ($i = 1; $i < 4; $i++) {
    $stock = rand(0, 100);
    $isCritical = rand(0, 1);
    $criticalStock = $isCritical === 0 ? rand($stock, 100) : rand(0, $stock);
    $product = new Product(
        id: $i,
        name: "Product" . $i,
        stock: $stock,
        criticalStock: $criticalStock
    );
    $tracker = new InventoryTracker();

    foreach ($subscribers as $subscriber) {
        $tracker->addNotifier($subscriber);
    }
    $tracker->processStockUpdate($product);
}
