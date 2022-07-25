<?php

use Codemanas\ZoomWooCommerceAddon\DataStore;

$query = new \WC_Order_Query( array(
	'limit'       => - 1,
	'orderby'     => 'date',
	'order'       => 'DESC',
	'customer_id' => get_current_user_id(),
	'status'      => array( 'completed', 'processing' ),
) );

$orders    = $query->get_orders();
$tbl_class = apply_filters( 'vczapi_woocommerce_addon_meeting_table_class', 'woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table woocommerce-zoom-meetings' );

$totalPages = 0;

global $wp;
$current_url = home_url( add_query_arg( array(), $wp->request ) );

$link = $current_url . '/?mpage=%d'; // default pagination link


// by default sort by meeting date ascending. For eg. July 3, July 4, July 5
$date_filter_opt          = 'asc';
$date_filter_opt_opposite = 'desc';

if ( ! empty( $_GET['date'] ) ) {
	if ( $_GET['date'] === 'desc' ) {
		$date_filter_opt          = 'desc';
		$date_filter_opt_opposite = 'asc';
		$link                     = $current_url . '/?mpage=%d&date=desc';

	} else {
		$date_filter_opt          = 'asc';
		$date_filter_opt_opposite = 'desc';
		$link                     = $current_url . '/?mpage=%d&date=asc';
	}
}

$date_filter_link = home_url( add_query_arg(
	array(
		'mpage' => ! empty( $_GET['mpage'] ) ? (int) $_GET['mpage'] : 1,
		'date'  => $date_filter_opt_opposite,
	), $wp->request ) );

?>
<div class="vczapi-woo-purchased-meetings-list">
    <table class="<?php echo $tbl_class; ?>">
        <thead>
        <tr>
            <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
                <span class="nobr"><?php _e( 'Order', 'vczapi-woocommerce-addon' ) ?></span>
            </th>
            <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
                <span class="nobr"><?php _e( 'Title', 'vczapi-woocommerce-addon' ) ?></span>
            </th>
            <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date">
                <span class="nobr"><?php _e( 'Date', 'vczapi-woocommerce-addon' ) ?></span>
                <a href="<?php echo $date_filter_link; ?>" class="<?php echo $date_filter_opt; ?>">
                    <img width="20" height="20"
                         src="<?php echo VZAPI_WOOCOMMERCE_ADDON_DIR_URI . 'assets/images/arrow-up-down.png'; ?>"
                         style="float: right;"/>
                </a>
            </th>
            <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-actions">
                <span class="nobr"><?php _e( 'Join', 'vczapi-woocommerce-addon' ) ?></span></th>
            <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-actions woocommerce-orders-table__meeting_post__header">
                <span class="nobr"><?php _e( 'Meeting Link', 'vczapi-woocommerce-addon' ) ?></span></th>
        </tr>
        </thead>
        <tbody>
		<?php
		$count              = 0;
		$meetings_container = array();
		$date_to_show       = '';

		if ( ! empty( $orders ) ) {
			foreach ( $orders as $order ) {
				$items = $order->get_items();
				if ( ! empty( $items ) ) {
					foreach ( $items as $item ) {
						$post_id = get_post_meta( $item->get_product_id(), '_vczapi_zoom_post_id', true );
						$exists  = get_the_title( $post_id );

						if ( ! empty( $post_id ) && ! empty( $exists ) ) {
							$meeting_details = get_post_meta( $post_id, '_meeting_zoom_details', true );

							$tz_for_check     = new DateTimeZone( 'UTC' );
							$dateTimeTomorrow = new DateTime( 'NOW', $tz_for_check );
							if ( ( true == apply_filters( 'vczapi_woocommerce_show_past_meetings_in_account_details', ( ! empty( get_option( 'vczapi_wc_hide_completed_meetings' ) ) ) ) )
							     && $meeting_details->type !== 8 
							     && $meeting_details->type !== 9 
							     && $meeting_details->type != 3
							     && $meeting_details->type != 4
							) {
								$meeting_date_to_check = vczapi_dateConverter( isset($meeting_details->start_time) ? $meeting_details->start_time:strtotime('NOW'), 'UTC', false );
								if ( $dateTimeTomorrow >= $meeting_date_to_check ) {
									continue;
								}
							} else if ( ( true == apply_filters( 'vczapi_woocommerce_show_past_meetings_in_account_details', ( ! empty( get_option( 'vczapi_wc_hide_completed_meetings' ) ) ) ) )
							            && ( $meeting_details->type == 8 || $meeting_details->type == 9 )
//					         && $meeting_details->type != 3
							) {
								if ( class_exists( 'Codemanas\ZoomPro\Helpers' ) ) {
									$last_occurrence       = end( $meeting_details->occurrences );
									$meeting_date_to_check = vczapi_dateConverter( $last_occurrence->start_time, 'UTC', false );
									if ( $dateTimeTomorrow >= $meeting_date_to_check ) {
										continue;
									}
								}
							}

							// orderinfo
							$order_info = '<a href="' . esc_url( $order->get_view_order_url() ) . '">' . esc_html( $order->get_id() ) . '</a>';


							// calculate date before displaying
							if ( isset( $meeting_details->type ) && ( $meeting_details->type === 8 || $meeting_details->type === 3 ) && vczapi_recurring_addon_active() ) {
								$meeting_details->occurrences = ! empty( $meeting_details->occurrences ) ? $meeting_details->occurrences : false;
								$now                          = new \DateTime( 'now -1 hour', new \DateTimeZone( $meeting_details->timezone ) );
								$next_occurence               = false;
								if ( $meeting_details->type === 8 && ! empty( $meeting_details->occurrences ) ) {
									foreach ( $meeting_details->occurrences as $occurrence ) {
										if ( $occurrence->status === "available" ) {
											$start_date = new \DateTime( $occurrence->start_time, new \DateTimeZone( $meeting_details->timezone ) );
											if ( $start_date >= $now ) {
												$next_occurence = $occurrence->start_time;
												break;
											}

//											$next_occurence = 'ended';
//											break;
										}
									}
								} else if ( $meeting_details->type === 3 ) {
									//No time fixed meeting
									$next_occurence = false;
								} else {
									//Set Past date
									$next_occurence = 'ended';
								}

								if ( ! $next_occurence ) {
									$next_occurence = __( 'No fixed time Meeting', 'vczapi-woocommerce-addon' );
									$date_to_show   = __( 'No fixed time Meeting', 'vczapi-woocommerce-addon' );
								} else if ( $next_occurence === "ended" ) {
									$next_occurence = __( 'Meeting Ended', 'vczapi-woocommerce-addon' );
									$date_to_show   = __( 'Meeting Ended', 'vczapi-woocommerce-addon' );
								} else {
									$next_occurence = vczapi_dateConverter( $next_occurence, $meeting_details->timezone, 'F j, Y, g:i a', true );
									$date_to_show   = $next_occurence;
								}
								// echo $next_occurence;
							} else {
								if ( isset( $meeting_details->start_time ) && isset( $meeting_details->timezone ) ) {
									$date_to_show = vczapi_dateConverter( $meeting_details->start_time, $meeting_details->timezone, 'F j, Y @ g:i a' );
								}
							}

							// join links
							if ( ! empty( $meeting_details ) && ! empty( $meeting_details->registration_url ) ) {
								$registration_details = get_user_meta( get_current_user_id(), '_vczapi_pro_registration_details', true );
								if ( ! empty( $registration_details ) && ! empty( $registration_details[ $meeting_details->id ] ) ) {
									$join_link = '<a target="_blank" class="vzapi-woo-join-meeting-btn" rel="nofollow" href="' . esc_url( $registration_details[ $meeting_details->id ]->join_url ) . '">' . __( "Join via App", "vczapi-woocommerce-addon" ) . '</a>';
								} else {
									$join_link = 'N/A';
								}
							} else {

								$join_link = '<a target="_blank" class="vzapi-woo-join-meeting-btn" rel="nofollow" href="' . esc_url( $meeting_details->join_url ) . '">' . __( "Join via App", "vczapi-woocommerce-addon" ) . '</a>';
								$disabled  = get_option( '_vczapi_woocommerce_disable_browser_join' );
								if ( empty( $disabled ) && ! empty( $meeting_details->password ) ) {
									$join_link .= ' / ' . DataStore::get_browser_join_link( $post_id, $meeting_details->password, $meeting_details->id );
								}
							}

							// Meeting link
							$meeting_link         = '<a class="vzapi-woo-join-meeting-btn" rel="nofollow" href="' . esc_url( get_permalink( $post_id ) ) . '">' . __( "View Meeting Details", "vczapi-woocommerce-addon" ) . '</a>';
							$meetings_container[] = array(
								'order_id'     => $order->get_id(),
								'order_info'   => $order_info,
								'date'         => $date_to_show,
								'join'         => $join_link,
								'meeting_link' => $meeting_link,
								'timezone'     => $meeting_details->timezone,
								'title'        => get_the_title( $post_id )
							);
							$count ++;
						}
					}
				}
			}

			// now we have all the meetings and its info into this container
			// preint( $meetings_container );

			// before that do some sorting
			// https://stackoverflow.com/questions/2910611/php-sort-a-multidimensional-array-by-element-containing-date/2910642
			if ( ! empty( $meetings_container ) ) {
				if ( $date_filter_opt === 'asc' ) {

					foreach ( $meetings_container as $key => $part ) {
						$sort[ $key ] = strtotime( $part['date'] );
					}
					array_multisort( $sort, SORT_ASC, $meetings_container );
				} else {
					foreach ( $meetings_container as $key => $part ) {
						$sort[ $key ] = strtotime( $part['date'] );
					}
					array_multisort( $sort, SORT_DESC, $meetings_container );
				}

				// lets do some pagination, shall we

				$page       = ! empty( $_GET['mpage'] ) ? (int) $_GET['mpage'] : 1;
				$total      = count( $meetings_container ); //total items in array
				$limit      = apply_filters( 'vczapi_woocommerce_meeting_order_posts_per_page', get_option( 'posts_per_page' ) );
				$totalPages = ceil( $total / $limit ); //calculate total pages
				$page       = max( $page, 1 ); //get 1 page when $_GET['page'] <= 0
				$page       = min( $page, $totalPages ); //get last page when $_GET['page'] > $totalPages
				$offset     = ( $page - 1 ) * $limit;
				if ( $offset < 0 ) {
					$offset = 0;
				}

				$meetings_container = array_slice( $meetings_container, $offset, $limit );

				foreach ( $meetings_container as $meeeting ) { // that meeeting extra "e" is intentional for now
					?>

                    <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-processing order">
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number"
                            data-title="Order">
							<?php echo $meeeting['order_info']; ?>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number"
                            data-title="title">
							<?php echo $meeeting['title']; ?>
                        </td>

                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date"
                            data-title="Date">
							<?php
							echo $meeeting['date'] . '<br /> ( ' . $meeeting['timezone'] . ' ) '; ?>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions"
                            data-title="Actions">
							<?php echo $meeeting['join']; ?>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions woocommerce-orders-table__meeting_post"
                            data-title="Actions">
							<?php echo $meeeting['meeting_link']; ?>
                        </td>
                    </tr>
					<?php
				}
			}
			if ( $count === 0 ) {
				\Codemanas\ZoomWooCommerceAddon\Orders::output_no_order_text();
			}
		} else {
			\Codemanas\ZoomWooCommerceAddon\Orders::output_no_order_text();
		}
		?>
        </tbody>
    </table>
	<?php
	// Next / Prev Pagination
	$pager_container = '<div class="pagic-meeting-pagination">';
	if ( $totalPages != 0 ) {
		if ( $page == 1 ) {
			$pager_container .= '<span></span>';
		} else {
			$pager_container .= sprintf( '<a href="' . $link . '" style="color: #c00"> &#171; prev</a>', $page - 1 );
		}
		$pager_container .= ' <span> <strong>' . $page . '</strong> of ' . $totalPages . '</span>';
		if ( $page == $totalPages ) {
			$pager_container .= '';
		} else {
			$pager_container .= sprintf( '<a href="' . $link . '" style="color: #c00"> next &#187; </a>', $page + 1 );
		}
	}
	$pager_container .= '</div>';
	echo $pager_container;
	?>
</div><!--vczapi-woo-purchased-meetings-list-->
