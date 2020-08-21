<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Kento\CustomPackingSlip\Model\Order\Pdf\Items\Shipment;

/**
 * Sales Order Shipment Pdf default items renderer
 */
class DefaultShipment extends \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
{
    /**
     * Core string
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;


    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;



    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;


    
    
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,

        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
     \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,    
     
     \Magento\Framework\Api\FilterBuilder $filterBuilder,
     \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,

        array $data = []
    ) {
        $this->string = $string;

        $this->orderItemRepository = $orderItemRepository;
       $this->searchCriteriaBuilder = $searchCriteriaBuilder;

       $this->filterGroupBuilder = $filterGroupBuilder;
       $this->filterBuilder = $filterBuilder;

        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Draw item line
     *
     * @return void
     */
    public function draw()
    {

    //     $nameFilter = $this->filterBuilder
    //         ->setField('item_id')
    //         ->setValue(1)
    //         ->setConditionType('eq')
    //         ->create();

    //     $searchCriteria = $this->searchCriteriaBuilder->addFilters([$nameFilter]);
    //    // echo '<pre>';
    //    // die($searchCriteria);
    //     $orderItemList = $this->orderItemRepository->getList($searchCriteria);

    //     if($orderItemList->getTotalCount() >= 0)
    //     {
    //         foreach ($orderItemList->getItems() as $orderItem)
    //         {
    //             echo '<pre>';
    //             print_r($orderItem->getData());
    //         }
    //     }


        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = [];

        $sku = $item->getSku();
        $itemId = $item->getData('order_item_id');


     // die(json_encode($item->getData()));
        // draw Product name
        if($sku != '106891')
            $lines[0] = [['text' => $this->string->split($item->getName(), 60, true, true), 'feed' => 110]];
        else{
            //die(print_r($productOptions,true));
            $this->searchCriteriaBuilder->addFilter('item_id', $itemId , 'eq');
            $collection = $this->orderItemRepository->getList(
                $this->searchCriteriaBuilder->create()
            );
            
            // die(print_r($collection->getData()[0],true));
               if(count($collection->getData()) > 0){
                        $productOptions = ($collection->getData()[0]['product_options']);
                        $decodedResult = json_decode($productOptions);
                        $options = $decodedResult->info_buyRequest->options;
              //  if(count($item->getData('product_options')) > 0) 
               
               } else
                    $options=($item->getData('product_options')['info_buyRequest']['options']);

                   
            // die(print_r($productOptions,true));
           // die(serialize($decodedResult->info_buyRequest->options));
            // $lines[0] = [['text' => serialize($productOptions),'feed' => 130]];
            $lines[0] = [['text' => $this->string->split($item->getName(), 60, true, true), 'feed' => 110]];
            $i=1;
           foreach ($options as $option => $optionValue) {
                
            switch ($option) {          
            case "28" : $key="Hear Us"; break;  // hear about us
            case "29" : $key="Backing Dimension"; break;  // backing dimension
            case "30" : $key="Placement"; break;  // Placement Direction
            case "31" : $key="Sew Yeah Choose Pattern"; break;  // Sewyeah Choose Pattern
            case "32" : $key="Trim Backing"; break;  // Trim Off Excess Backing
            case "33" : $key="Thread Color"; break;  // Thread Color
            case "34" : $key="Bating"; break;  // Batting
            case "35" : $key="Additional Note"; break;  // Additional Notes
            case "36" : $key="Pattern Theme"; break;  // pattern themes
            case "37" : $key="Initials"; break;
            default: $key=$option; break;
        }


              //  draw options label
                $lines[$i][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($key)." : " , 70, true, true),
                    'font' => 'bold',
                    'feed' => 120,
                ];

                $lines[$i][] = [
                    'text' => $this->string->split($this->filterManager->stripTags($optionValue), 85, true, true),
                    'font' => 'italic',
                    'feed' => strlen($this->filterManager->stripTags($key))*5 +125,
                ];
                $i++;

           }
            }
           // else{
              //  $lines[0] = [['text' => $this->string->split($item->getName(), 60, true, true), 'feed' => 110]];

           // }
        //}
           
        // draw QTY
        $lines[0][] = ['text' => $item->getQty() * 1, 'feed' => 35];
        
        // draw Pulled
        $lines[0][] = ['text' => $this->string->split('[  ]', 60, true, true), 'feed' => 75];
       
              
        // draw Location
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getProductId());
        $lines[0][] = ['text' => $this->string->split($product->getAttributeText('location'), 60, true, true), 'feed' => 395];

        // draw SKU
        $lines[0][] = [
            'text' => $this->string->split($this->getSku($item), 25),
            'feed' => 565,
            'align' => 'right',
        ];

        // // Custom options
        // $options = $this->getItemOptions();
        // if ($options) {
        //     foreach ($options as $option) {
                
        //         // draw options label
        //         $lines[][] = [
        //             'text' => $this->string->split($this->filterManager->stripTags($option['label']), 70, true, true),
        //             'font' => 'italic',
        //             'feed' => 110,
        //         ];

        //         // draw options value
        //         if ($option['value'] !== null) {
        //             $printValue = isset(
        //                 $option['print_value']
        //             ) ? $option['print_value'] : $this->filterManager->stripTags(
        //                 $option['value']
        //             );
        //             $values = explode(', ', $printValue);
        //             foreach ($values as $value) {
        //                 $lines[][] = ['text' => $this->string->split($value, 50, true, true), 'feed' => 115];
        //             }
        //         }
        //     }
        // }

        $lineBlock = ['lines' => $lines, 'height' => 20];

        $page = $pdf->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $this->setPage($page);
    }
}
