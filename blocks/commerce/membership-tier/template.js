import { __ } from '@wordpress/i18n';

export function createMembershipTierTemplate( overrides = {} ) {
	const title = overrides.title || __( 'Membership Tier', 'pns-blocks' );
	const buttonText =
		overrides.buttonText || __( 'Choose this tier', 'pns-blocks' );
	const url = overrides.url || '#';

	return [
		[
			'core/cover',
			{
				className: 'pns-membership-tier__media',
				dimRatio: 0,
				lock: {
					move: true,
					remove: true,
				},
			},
			[],
		],
		[
			'core/group',
			{
				className: 'pns-membership-tier__introduction',
				lock: {
					move: true,
					remove: true,
				},
			},
			[
				[
					'core/heading',
					{
						className: 'pns-membership-tier__title',
						content: `<a href="${ url }">${ title }</a>`,
						level: 3,
					},
				],
				[
					'core/paragraph',
					{
						className: 'pns-membership-tier__tagline',
						content:
							'<strong>' +
							__(
								'Summarise the impact of this tier.',
								'pns-blocks'
							) +
							'</strong>',
					},
				],
				[
					'core/paragraph',
					{
						className: 'pns-membership-tier__description',
						content: __(
							'Explain what this membership makes possible.',
							'pns-blocks'
						),
					},
				],
				[
					'core/paragraph',
					{
						className: 'pns-membership-tier__description',
						content: __(
							'Add optional supporting details or leave this paragraph empty.',
							'pns-blocks'
						),
					},
				],
			],
		],
		[
			'core/paragraph',
			{
				className: 'pns-membership-tier__price',
				content: __( '£0 / month', 'pns-blocks' ),
			},
		],
		[
			'core/buttons',
			{
				className: 'pns-membership-tier__action',
				lock: {
					move: true,
					remove: true,
				},
			},
			[
				[
					'core/button',
					{
						className: 'pns-membership-tier__button',
						lock: {
							move: true,
							remove: true,
						},
						text: buttonText,
						url,
					},
				],
			],
		],
		[
			'core/list',
			{
				className: 'pns-membership-tier__benefits',
				lock: {
					move: true,
					remove: true,
				},
			},
			[
				[
					'core/list-item',
					{
						content: __(
							'Add a membership benefit.',
							'pns-blocks'
						),
					},
				],
				[
					'core/list-item',
					{
						content: __(
							'Add another membership benefit.',
							'pns-blocks'
						),
					},
				],
			],
		],
	];
}
