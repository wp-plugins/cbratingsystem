<?php

class CBRatingSystemCalculation extends CBRatingSystem {


	public static function singleValueCalculation( $valueArray ) {
		if ( ! empty( $valueArray ) ) {
			if ( isset( $valueArray['value'] ) and ( isset( $valueArray['criteria_array'] ) or isset( $valueArray['count'] ) ) ) {
				$count = ( isset( $valueArray['count'] ) ) ? $valueArray['count'] : count( $valueArray['criteria_array'] );
				$value = $valueArray['value'];

				//$result = ( ( $value / $count ) * 100 );        // Like: 70 ==> 0.70 * 100
				$result = $value;

				return (int) $result;
			}
		}
	}

	public static function perUserPerPostCriteriaAverageCalculation( &$criteriasArray ) {
		if ( ! empty( $criteriasArray ) ) {
			foreach ( $criteriasArray as $criteriaId => $criteriaArray ) {
				if ( isset( $criteriaArray['value'] ) and ( isset( $criteriaArray['criteria_array'] ) or isset( $criteriaArray['count'] ) ) ) {
					//$avg = (int) self::singleValueCalculation($criteriaArray);
					$avg                                    = $criteriaArray['value'];
					$criteriasArray[$criteriaId]['average'] = $avg;
					$avgValue[$criteriaId]                  = $avg;
				}
			}

			if ( isset( $avgValue ) ) {
				$totalSum = array_sum( $avgValue );
				$count    = count( $avgValue );

				$result = ( $totalSum / $count ); // Like: 60.5

				return $result;
			}
		}
	}

	public static function allUserPerPostCriteriaAverageCalculation( &$data, $post_id ) {
		if ( ! empty( $data['ratingsValueArray'] ) ) {
			foreach ( $data['ratingsValueArray'] as $k => $ratingArray ) {
				if ( is_array( $ratingArray ) and isset( $ratingArray['criterias'] ) ) {
					$criteriasArray                                = $ratingArray['criterias'];
					$avgValue[$k]                                  = self::perUserPerPostCriteriaAverageCalculation( $criteriasArray );
					$data['ratingsValueArray'][$k]['avgPerRating'] = $avgValue;
					$data['avgRatingArray']['avgPerRating']        = $avgValue;
					$data['ratingsValueArray'][$k]['criterias']    = $criteriasArray;

					//echo '<pre><strong>criterias:</strong> '; print_r($ratingArray['criterias']); echo '</pre>';

					if ( ! empty( $data['userIdToMatch'] ) ) {
						$iCOunt = 0;
						foreach ( $data['userIdToMatch'] as $userType => $userIds ) {
							if ( ( $userIds == - 1 ) && ( $data['ratingsValueArray'][$k]['user_id'] > 0 ) ) {
								$customUserAvgValue[$userType][$k] = self::perUserPerPostCriteriaAverageCalculation( $criteriasArray );

								//break;
							} elseif ( ( is_array( $userIds ) ) && ( in_array( $data['ratingsValueArray'][$k]['user_id'], $userIds ) ) ) {
								$customUserAvgValue[$userType][$k] = self::perUserPerPostCriteriaAverageCalculation( $criteriasArray );

							}
						}

					}

					foreach ( $ratingArray['criterias'] as $cId => $criteria ) {
						//$avg = (int) self::singleValueCalculation($criteria);
						$avg                        = $criteria['value'];
						$avgCriteriaValue[$cId][$k] = $avg;

						if ( ! empty( $data['userIdToMatch'] ) ) {

							foreach ( $data['userIdToMatch'] as $userType => $userIds ) {
								if ( ( $userIds == - 1 ) && ( $data['ratingsValueArray'][$k]['user_id'] > 0 ) ) {
									$customUserPerCriteriaAvgValue[$userType][$cId][$k] = $avg;

								} elseif ( ( is_array( $userIds ) ) && ( in_array( $data['ratingsValueArray'][$k]['user_id'], $userIds ) ) ) {
									$customUserPerCriteriaAvgValue[$userType][$cId][$k] = $avg;

								}
							}
						}

						$data['criteriaAverageArray']['criteria'][$cId][$k]      = $avg;
						$data['avgRatingArray']['avgPerCriteria'][$cId]['label'] = $data['criteria'][$cId]['label'];
						$data['avgRatingArray']['avgPerCriteria'][$cId]['stars'] = $data['criteria'][$cId]['stars'];
					}
				}
			}

			//echo '<pre><strong>criterias:</strong> '; print_r($avgCriteriaValue); echo '</pre>';
			//echo '<pre><strong>criterias:</strong> '; print_r($avgValue); echo '</pre>'; die();

			if ( isset( $avgCriteriaValue ) ) {
				foreach ( $avgCriteriaValue as $cId => $avg ) {
					$totalSum = array_sum( $avg );
					$count    = count( $avg );

					$result = ( $totalSum / $count ); // Like: 60.5

					$data['avgRatingArray']['avgPerCriteria'][$cId]['value'] = $result;
				}
			}

			if ( isset( $avgValue ) ) {
				$totalSum = array_sum( $avgValue );
				$count    = count( $avgValue );

				$result = ( $totalSum / $count ); // Like: 60.5

				$data['avgRatingArray']['perPost'][$post_id] = $result;

				//return (int) $result;
			}

			if ( ! empty( $customUserAvgValue ) ) {
				foreach ( $customUserAvgValue as $userType => $avgs ) {
					$totalSum = array_sum( $avgs );
					$count    = count( $avgs );

					$result = ( $totalSum / $count ); // Like: 60.5

					$data['avgRatingArray']['customUser']['perPost'][$userType]          = round( $result );
					$data['avgRatingArray']['customUser']['perPostRateCount'][$userType] = count( $customUserAvgValue[$userType] );

					if ( ! empty( $customUserPerCriteriaAvgValue[$userType] ) ) {
						foreach ( $customUserPerCriteriaAvgValue[$userType] as $cId => $avg ) {
							$totalSum = array_sum( $avg );
							$count    = count( $avg );

							$result                                                                        = ( $totalSum / $count ); // Like: 60.5
							$data['avgRatingArray']['customUser']['perCriteria'][$userType][$cId]['value'] = round( $result );
						}
					}
				}

				//echo '<pre>'; print_r($data['avgRatingArray']['customUser']['perPostRateCount']); echo '</pre>'; die();

			}
		}
	}

	public static function allUserPerCriteriaAverageCalculation( &$data, $post_id ) {
		return self::allUserPerPostCriteriaAverageCalculation( $data, $post_id );

		/*
		if(!empty($data['ratingsValueArray'])) {
			foreach($data['ratingsValueArray'] as $k => $ratingArray) {
				if(is_array($ratingArray) and isset($ratingArray['criterias'])) {
					foreach($ratingArray['criterias'] as $cId => $criteria) {

					}
				}
			}

			if(isset($avgValue)) {
				$totalSum = array_sum($avgValue);
				$count = count($avgValue);

				$result = ( $totalSum / $count );           // Like: 60.5

				return (int) $result;
			}
		}
		*/

	}


}