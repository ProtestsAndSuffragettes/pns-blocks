import { InnerBlocks, RichText, useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';
import { createElement as el } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { createMembershipTierTemplate } from '../membership-tier/template';
import './editor.css';
import './style.css';

const allowedBlocks = [ 'pns/membership-tier' ];
const membershipTiersTemplate = [
	[
		'pns/membership-tier',
		{},
		createMembershipTierTemplate( {
			title: __( 'Membership Tier 1', 'pns-blocks' ),
			buttonText: __( 'Choose Tier 1', 'pns-blocks' ),
		} ),
	],
	[
		'pns/membership-tier',
		{},
		createMembershipTierTemplate( {
			title: __( 'Membership Tier 2', 'pns-blocks' ),
			buttonText: __( 'Choose Tier 2', 'pns-blocks' ),
		} ),
	],
	[
		'pns/membership-tier',
		{},
		createMembershipTierTemplate( {
			title: __( 'Membership Tier 3', 'pns-blocks' ),
			buttonText: __( 'Choose Tier 3', 'pns-blocks' ),
		} ),
	],
	[
		'pns/membership-tier',
		{},
		createMembershipTierTemplate( {
			title: __( 'Membership Tier 4', 'pns-blocks' ),
			buttonText: __( 'Choose Tier 4', 'pns-blocks' ),
		} ),
	],
];

function MembershipTiersEdit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps( {
		className: 'pns-membership-tiers alignwide',
	} );

	return el(
		'div',
		blockProps,
		el( RichText, {
			className: 'pns-membership-tiers__heading',
			onChange: ( heading ) => setAttributes( { heading } ),
			tagName: 'h2',
			value: attributes.heading,
		} ),
		el(
			'div',
			{ className: 'pns-membership-tiers__grid' },
			el( InnerBlocks, {
				allowedBlocks,
				renderAppender: InnerBlocks.ButtonBlockAppender,
				template: membershipTiersTemplate,
				templateLock: false,
			} )
		)
	);
}

registerBlockType( 'pns/membership-tiers', {
	icon: 'grid-view',
	edit: MembershipTiersEdit,
	save( { attributes } ) {
		return el(
			'div',
			useBlockProps.save( {
				className: 'pns-membership-tiers',
			} ),
			el( RichText.Content, {
				className: 'pns-membership-tiers__heading',
				tagName: 'h2',
				value: attributes.heading,
			} ),
			el(
				'div',
				{ className: 'pns-membership-tiers__grid' },
				el( InnerBlocks.Content )
			)
		);
	},
} );
