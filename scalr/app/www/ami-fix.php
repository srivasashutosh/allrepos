<?php

@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(1);

require ("src/prepend.inc.php");

$imageId = $_GET['imageId'];

$envId = Scalr_Session::getInstance()->getEnvironmentId();
/* @var $env Scalr_Environment */
$env = Scalr_Environment::init()->loadById($envId);
$aws = $env->aws('eu-west-1');
print "Retrieving AMI information...";

try {
    /* @var $imageInfo Scalr\Service\Aws\Ec2\DataType\ImageData */
    $imageInfo = $aws->ec2->image->describe($imageId)->get(0);
    print "<span style='color:green;'>OK</span><br>";
} catch (Exception $e) {
    print "<span style='color:red;'>ERROR: {$e->getMessage()}</span><br>";
    exit();
}
if ($imageInfo->rootDeviceType == 'ebs') {
    $snapId = count($imageInfo->blockDeviceMapping) ?
              $imageInfo->blockDeviceMapping->get(0)->ebs->snapshotId : null;

    if ($snapId) {
        print "Root device type is EBS. Snapshot ID: <span style='color:green;'>{$snapId}</span><br>";
    } else {
        print "<span style='color:red;'>Cannot get snapshotID from AMI info</span><br>";
        exit();
    }

    print "Retrieving Snapshot information...";
    try {
        /* @var $snapshotInfo Scalr\Service\Aws\Ec2\DataType\SnapshotData */
        $snapshotInfo = $aws->ec2->snapshot->describe(snapId)->get(0);
        if ($snapshotInfo) {
            if ($snapshotInfo->status == Scalr\Service\Aws\Ec2\DataType\SnapshotData::STATUS_ERROR) {

                print "<span style='color:green;'>OK</span><br>";
                print "Searching for recovered snapshot...";

                $allSnapshots = $aws->ec2->snapshot->describe();
                /* @var $snap Scalr\Service\Aws\Ec2\DataType\SnapshotData */
                foreach ($allSnapshots as $snap) {
                    if (stristr($snap->description, $snapId)) {
                        $newSnapshotId = $snap->snapshotId;
                        break;
                    }
                }

                if (!empty($newSnapshotId)) {

                    print "<span style='color:green;'>OK</span><br>";

                    $ebsbdm = new Scalr\Service\Aws\Ec2\DataType\EbsBlockDeviceData();
                    $ebsbdm->snapshotId = $newSnapshotId;
                    $blockDeviceMapping = new Scalr\Service\Aws\Ec2\DataType\BlockDeviceMappingData();
                    $blockDeviceMapping->setEbs($ebsbdm);
                    $registerImageType = new Scalr\Service\Aws\Ec2\DataType\RegisterImageData(
                        $imageInfo->name . "-restored",
                        $blockDeviceMapping
                    );
                    $registerImageType->description = $registerImageType->name;
                    $registerImageType->architecture = $imageInfo->architecture;
                    if ($imageInfo->kernelId) {
                        $registerImageType->kernelId = $imageInfo->kernelId;
                    }
                    if ($imageInfo->ramdiskId) {
                        $registerImageType->ramdiskId = $imageInfo->ramdiskId;
                    }
                    $registerImageType->rootDeviceName = $imageInfo->rootDeviceName;

                    print "Registering new AMI...";

                    $newImageId = $aws->ec2->image->register($registerImageType);

                    print "<span style='color:green;'>OK</span>. New AMI id: {$res->imageId}<br>";

                    print "Updating Scalr database AMI...";

                    $roleId = $db->GetOne("SELECT role_id FROM role_images WHERE image_id = ?", array($imageId));
                    if ($roleId) {
                        $dbRole = DBRole::loadById($roleId);
                        if ($dbRole->clientId = Scalr_Session::getInstance()->getClientId()) {
                            $db->Execute("UPDATE role_images SET image_id=? WHERE image_id=?", array($newImageId, $imageId));
                        }
                    }

                    print "<span style='color:green;'>OK</span>. AMI successfully repaired.";

                } else {
                    print "<span style='color:red;'>Cannot find recovered snapshot.</span><br>";
                    exit();
                }
            } else {
                print "<span style='color:red;'>Snapshot is okay. No need to replace it.</span><br>";
                exit();
            }
        } else {
            print "<span style='color:red;'>ERROR: SnapshotID not found</span><br>";
            exit();
        }
    } catch (Exception $e) {
        print "<span style='color:red;'>ERROR: {$e->getMessage()}</span><br>";
        exit();
    }
}