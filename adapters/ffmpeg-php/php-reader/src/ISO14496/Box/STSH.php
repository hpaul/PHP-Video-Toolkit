<?php
/**
 * PHP Reader Library
 *
 * Copyright (c) 2008 The PHP Reader Project Workgroup. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the project workgroup nor the names of its
 *    contributors may be used to endorse or promote products derived from this
 *    software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Id$
 */

/**#@+ @ignore */
require_once("ISO14496/Box/Full.php");
/**#@-*/

/**
 * The <i>Shadow Sync Sample Box</i> table provides an optional set of sync
 * samples that can be used when seeking or for similar purposes. In normal
 * forward play they are ignored.
 *
 * Each entry in the Shadow Sync Table consists of a pair of sample numbers. The
 * first entry (shadowedSampleNumber) indicates the number of the sample that a
 * shadow sync will be defined for. This should always be a non-sync sample
 * (e.g. a frame difference). The second sample number (syncSampleNumber)
 * indicates the sample number of the sync sample (i.e. key frame) that can be
 * used when there is a random access at, or before, the shadowedSampleNumber.
 *
 * The shadow sync samples are normally placed in an area of the track that is
 * not presented during normal play (edited out by means of an edit list),
 * though this is not a requirement. The shadow sync table can be ignored and
 * the track will play (and seek) correctly if it is ignored (though perhaps not
 * optimally).
 *
 * The Shadow Sync Sample replaces, not augments, the sample that it shadows
 * (i.e. the next sample sent is shadowedSampleNumber+1). The shadow sync sample
 * is treated as if it occurred at the time of the sample it shadows, having the
 * duration of the sample it shadows.
 *
 * Hinting and transmission might become more complex if a shadow sample is used
 * also as part of normal playback, or is used more than once as a shadow. In
 * this case the hint track might need separate shadow syncs, all of which can
 * get their media data from the one shadow sync in the media track, to allow
 * for the different time-stamps etc. needed in their headers.
 *
 * @package    php-reader
 * @subpackage ISO 14496
 * @author     Sven Vollbehr <svollbehr@gmail.com>
 * @copyright  Copyright (c) 2008 The PHP Reader Project Workgroup
 * @license    http://code.google.com/p/php-reader/wiki/License New BSD License
 * @version    $Rev$
 */
final class ISO14496_Box_STSH extends ISO14496_Box_Full
{
  /** @var Array */
  private $_shadowSyncSampleTable = array();
  
  /**
   * Constructs the class with given parameters and reads box related data from
   * the ISO Base Media file.
   *
   * @param Reader $reader The reader object.
   */
  public function __construct($reader, &$options = array())
  {
    parent::__construct($reader, $options);
    
    $entryCount = $this->_reader->readUInt32BE();
    $data = $this->_reader->read
      ($this->getOffset() + $this->getSize() - $this->_reader->getOffset());
    for ($i = 0; $i < $entryCount; $i++)
      $this->_shadowSyncSampleTable[$i] = array
        ("shadowedSampleNumber" =>
           Transform::fromUInt32BE(substr($data, ($i - 1) * 8, 4)),
         "syncSampleNumber" =>
           Transform::fromUInt32BE(substr($data, $i * 8 - 4, 4)));
  }
  
  /**
   * Returns an array of values. Each entry is an array containing the following
   * keys.
   *   o shadowedSampleNumber - gives the number of a sample for which there is
   *     an alternative sync sample.
   *   o syncSampleNumber - gives the number of the alternative sync sample.
   *
   * @return Array
   */
  public function getShadowSyncSampleTable()
  {
    return $this->_shadowSyncSampleTable;
  }
}
