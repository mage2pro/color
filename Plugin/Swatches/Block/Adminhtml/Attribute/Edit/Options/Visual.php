<?php
namespace Dfe\Color\Plugin\Swatches\Block\Adminhtml\Attribute\Edit\Options;
use Dfe\Color\Params as P;
use Dfe\Color\Setup\UpgradeSchema as Schema;
use Magento\Framework\DB\Select;
use Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\Visual as Sb;
// 2019-08-23
final class Visual {
	/**
	 * 2019-08-23
	 * @see \Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\Visual::getJsonConfig()
	 */
	function afterGetJsonConfig(Sb $sb, string $r):string {
		// 2019-08-23 df_json_decode() does not work here
		$a = json_decode($r, true); /** @var array(string => mixed) $a */
		$k = 'attributesData'; /** @var string $k */
		$ad = $a[$k]; /** @var array(array(string => mixed)) $ad */
		$ids = df_int_simple(array_column($ad, 'id')); /** @var int[] $ids */
		$s = df_db_from('eav_attribute_option_swatch', ['option_id', Schema::F]); /** @var Select $s */
		$s->where('option_id IN(?) AND 0 = store_id AND 1 = type', $ids);
		$d = df_conn()->fetchPairs($s); /** @var array(int => string) $d */
		// 2019-08-29
		// We can not use a deep array like ['mage2pro_color'][$i]
		// because vendor/mage2pro/color/view/adminhtml/templates/catalog/product/attribute/visual.phtml
		// fails in this case on a new row.
		$a[$k] = df_map($a[$k], function(array $a) use ($d) {return $a + df_map_kr(
			function($i, $v) {return [Schema::F . $i, $v];}
			,df_eta(df_json_decode(dfa($d, intval($a['id'])))) + array_fill(0, P::COUNT, null)
		);});
		return json_encode($a);
	}
}