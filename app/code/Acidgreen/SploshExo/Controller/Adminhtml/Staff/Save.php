<?php
namespace Acidgreen\SploshExo\Controller\Adminhtml\Staff;

use Magento\Backend\App\Action;
use Acidgreen\SploshExo\Model\Staff;
use Splosh\SalesRep\Helper\Structure;
use Acidgreen\SploshExo\Model\ImageUploader;

class Save extends Action
{
    /**
     * @var \Acidgreen\SploshExo\Model\Staff
     */
    protected $staff;

    /**
     * @var Structure
     */
    protected $structureHelper;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ImageUploader
     */
    protected $uploader;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param Staff $staff
     * @param Structure $structureHelper
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Action\Context $context,
        Staff $staff,
        Structure $structureHelper,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        ImageUploader $imageUploader
    )
    {
        parent::__construct($context);
        $this->staff = $staff;
        $this->structureHelper = $structureHelper;
        $this->dataPersistor = $dataPersistor;
        $this->uploader = $imageUploader;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $staff = $this->staff->load($id);

        if (!$id || !$staff->getId()) {
            $this->messageManager->addError('Invalid Staff Id.');
            $this->_redirect('*/*/');
            return;
        }

        $params = $this->getRequest()->getParams();
        $staffDataMapping = $this->structureHelper->getStaffColumns();
        $toSaveData = [];

        foreach ($staffDataMapping as $item) {
            $toSaveData[$item] = $params[$item];
            if ($item == 'photo' && isset($params[$item])) {
                $toSaveData[$item] = $params[$item][0]['name'];
            } else {
                $toSaveData[$item] = $params[$item];
            }
        }

        $image = $this->uploader->uploadAndSavePhoto('photo', $params);
        $toSaveData['photo'] = $image;

        $staff->setData($toSaveData);
        $staff->save();

        $this->_redirect('*/*/edit', ['id' => $staff->getId()]);
    }
}