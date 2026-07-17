import {
	InspectorControls,
	InnerBlocks,
	useBlockProps,
} from "@wordpress/block-editor";
import {
	createBlock,
	createBlocksFromInnerBlocksTemplate,
	getBlockType,
	getBlockVariations,
	registerBlockType,
	registerBlockVariation,
} from "@wordpress/blocks";
import {
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOptionIcon as ToggleGroupControlOptionIcon,
	__experimentalToolsPanelItem as ToolsPanelItem,
	PanelBody,
	SelectControl,
} from "@wordpress/components";
import { subscribe, useDispatch, useSelect } from "@wordpress/data";
import { createElement as el, Fragment } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import "./editor.css";
import "./style.css";

const defaultLayoutVariant = "media-right";
const textTextMediaType = "text";
const legacyTextTextLayoutVariants = [
	"text-text",
	"text-text-reversed",
	"text-text-constrained",
	"text-text-constrained-reversed",
];
const layoutOptions = [
	{
		label: __("Media left", "pns-blocks"),
		value: "media-left",
	},
	{
		label: __("Media right", "pns-blocks"),
		value: "media-right",
	},
	{
		label: __("Edge media left", "pns-blocks"),
		value: "edge-media-left",
	},
	{
		label: __("Edge media right", "pns-blocks"),
		value: "edge-media-right",
	},
];
const allowedLayoutVariants = layoutOptions.map(function (option) {
	return option.value;
});
const defaultMediaType = "image";
const allowedMediaTypes = ["image", "slideshow", "text", "video"];
const defaultTextVerticalAlignment = "center";
const textVerticalAlignmentOptions = [
	{
		label: __("Top", "pns-blocks"),
		value: "top",
	},
	{
		label: __("Centre", "pns-blocks"),
		value: defaultTextVerticalAlignment,
	},
	{
		label: __("Bottom", "pns-blocks"),
		value: "bottom",
	},
];
const copyGroupAttributes = {
	className: "pns-split-section__copy",
};
const SPLIT_SECTION_ICON = el(
	"svg",
	{
		"aria-hidden": "true",
		focusable: "false",
		viewBox: "0 0 24 24",
		xmlns: "http://www.w3.org/2000/svg",
	},
	el("path", {
		d: "M4 5h7v14H4V5Zm9 0h7v14h-7V5Z",
		fill: "currentColor",
	}),
);
const SPLIT_IMAGE_ICON = el(
	"svg",
	{
		"aria-hidden": "true",
		focusable: "false",
		viewBox: "0 0 24 24",
		xmlns: "http://www.w3.org/2000/svg",
	},
	el("path", {
		d: "M4 5h7v14H4V5Zm9 0h7v14h-7V5Zm1.5 10.5h4l-1.15-1.55-.9 1.1-1.05-1.45-.9 1.9Z",
		fill: "currentColor",
	}),
	el("circle", {
		cx: "16.5",
		cy: "9",
		fill: "#fff",
		r: "1",
	}),
);
const SPLIT_VIDEO_ICON = el(
	"svg",
	{
		"aria-hidden": "true",
		focusable: "false",
		viewBox: "0 0 24 24",
		xmlns: "http://www.w3.org/2000/svg",
	},
	el("path", {
		d: "M4 5h7v14H4V5Zm9 0h7v14h-7V5Zm2.5 4v6l4-3-4-3Z",
		fill: "currentColor",
	}),
);
const SPLIT_SLIDESHOW_ICON = el(
	"svg",
	{
		"aria-hidden": "true",
		focusable: "false",
		viewBox: "0 0 24 24",
		xmlns: "http://www.w3.org/2000/svg",
	},
	el("path", {
		d: "M4 5h7v14H4V5Zm9 0h7v14h-7V5Zm1.5 3h3.5v5.5h-3.5V8Zm1.5 7h3.5v1.5H16V15Z",
		fill: "currentColor",
	}),
);

const copyTemplate = [
	[
		"core/heading",
		{
			content: __("Section Heading", "pns-blocks"),
		},
	],
	[
		"core/paragraph",
		{
			content: __(
				"Use this split section for focused copy with related media. Replace this starter text before publishing.",
				"pns-blocks",
			),
		},
	],
	[
		"core/paragraph",
		{
			className: "pns-split-section__cta",
			content:
				'<a class="wp-block-button__link wp-element-button" href="#">' +
				__("Find out more", "pns-blocks") +
				"</a>",
		},
	],
];
const imageMediaTemplate = [
	[
		"core/image",
		{
			id: 1122,
			sizeSlug: "large",
			linkDestination: "none",
			url: "/wp-content/uploads/2022/08/Our_work_with_Wikipedia_Image@2x-1024x912.jpeg",
		},
	],
];

const videoMediaTemplate = [["core/video", {}]];

const slideshowMediaTemplate = [
	[
		"jetpack/slideshow",
		{
			ids: [2077, 2098, 2091, 2327, 2074, 2350, 1122],
			sizeSlug: "square",
		},
	],
];

function splitSectionTemplate(mediaTemplate, copyText, mediaColumnClassName) {
	return [
		[
			"core/columns",
			{
				align: "full",
				className: "pns-split-section__columns",
			},
			[
				[
					"core/column",
					{
						className: "pns-split-section__copy-column",
					},
					[
						[
							"core/group",
							copyGroupAttributes,
							copyTemplate.map(function (block) {
								if (
									block[0] !== "core/paragraph" ||
									block[1].className ||
									!copyText
								) {
									return block;
								}

								return [
									block[0],
									{
										...block[1],
										content: copyText,
									},
								];
							}),
						],
					],
				],
				[
					"core/column",
					{
						className:
							mediaColumnClassName ||
							"pns-split-section__media-column",
					},
					mediaTemplate,
				],
			],
		],
	];
}

const imageTemplate = splitSectionTemplate(
	imageMediaTemplate,
	__(
		"Use this split section for focused copy with a related image. Replace this starter text before publishing.",
		"pns-blocks",
	),
);
const videoTemplate = splitSectionTemplate(
	videoMediaTemplate,
	__(
		"Use this split section for focused copy with a related video. Replace this starter text before publishing.",
		"pns-blocks",
	),
	"pns-split-section__media-column pns-split-section__media-column--video",
);
const slideshowTemplate = splitSectionTemplate(
	slideshowMediaTemplate,
	__(
		"Use this split section for focused copy with a related image slideshow. Replace this starter text before publishing.",
		"pns-blocks",
	),
);

function textColumnTemplate(backgroundColor, heading, body, columnRole) {
	return [
		"core/column",
		{
			backgroundColor,
			className:
				"pns-split-section__" +
				columnRole +
				"-column pns-split-section__text-column",
			textColor: "neutral-0",
		},
		[
			[
				"core/group",
				copyGroupAttributes,
				[
					["core/heading", { content: heading }],
					[
						"core/paragraph",
						{
							content: body,
							fontSize: "text-lead",
						},
					],
					[
						"core/paragraph",
						{
							content: __(
								"Add supporting copy, links, or buttons for this panel.",
								"pns-blocks",
							),
						},
					],
				],
			],
		],
	];
}

const firstTextPanelTemplate = textColumnTemplate(
	"brand-purple",
	__("First Panel", "pns-blocks"),
	__(
		"Use this panel for the first part of your paired message.",
		"pns-blocks",
	),
	"copy",
);
const secondTextPanelTemplate = textColumnTemplate(
	"heritage-green",
	__("Second Panel", "pns-blocks"),
	__(
		"Use this panel for the second part of your paired message.",
		"pns-blocks",
	),
	"copy",
);
const textTextTemplate = [
	[
		"core/columns",
		{
			align: "full",
			className: "pns-split-section__columns",
		},
		[firstTextPanelTemplate, secondTextPanelTemplate],
	],
];

function normaliseLayoutVariant(layoutVariant) {
	return allowedLayoutVariants.includes(layoutVariant)
		? layoutVariant
		: defaultLayoutVariant;
}

function isTextTextLayout(attributes) {
	return (
		attributes.mediaType === textTextMediaType ||
		legacyTextTextLayoutVariants.includes(attributes.layoutVariant)
	);
}

function normaliseTextTextLayoutVariant(layoutVariant) {
	const legacyLayoutMap = {
		"text-text": "edge-media-right",
		"text-text-reversed": "edge-media-left",
		"text-text-constrained": "media-right",
		"text-text-constrained-reversed": "media-left",
	};

	return (
		legacyLayoutMap[layoutVariant] ||
		normaliseLayoutVariant(layoutVariant || "edge-media-right")
	);
}

function normaliseMediaType(mediaType) {
	return allowedMediaTypes.includes(mediaType) ? mediaType : defaultMediaType;
}

function normaliseTextVerticalAlignment(alignment) {
	return textVerticalAlignmentOptions.some(function (option) {
		return option.value === alignment;
	})
		? alignment
		: defaultTextVerticalAlignment;
}

function getTextVerticalAlignmentClassName(panel, alignment) {
	const normalisedAlignment = normaliseTextVerticalAlignment(alignment);

	return normalisedAlignment === defaultTextVerticalAlignment
		? ""
		: "is-pns-" + panel + "-text-align-" + normalisedAlignment;
}

function hasJetpackSlideshow() {
	return Boolean(getBlockType("jetpack/slideshow"));
}

function getMediaColumn(block) {
	const columns = block?.innerBlocks?.find(function (innerBlock) {
		return innerBlock.name === "core/columns";
	});

	return (
		columns?.innerBlocks?.find(function (innerBlock) {
			return innerBlock.attributes?.className?.includes(
				"pns-split-section__media-column",
			);
		}) || columns?.innerBlocks?.[1]
	);
}

function getMediaTypeFromColumn(mediaColumn) {
	const mediaBlockName = mediaColumn?.innerBlocks?.[0]?.name;
	const columnClassName = mediaColumn?.attributes?.className || "";

	if (columnClassName.includes("pns-split-section__text-column")) {
		return textTextMediaType;
	}

	if (mediaBlockName === "jetpack/slideshow") {
		return "slideshow";
	}

	if (mediaBlockName === "core/video" || mediaBlockName === "core/embed") {
		return "video";
	}

	return defaultMediaType;
}

function getMediaColumnClassName(mediaType) {
	return [
		"pns-split-section__media-column",
		...(mediaType === "video"
			? ["pns-split-section__media-column--video"]
			: []),
	].join(" ");
}

function createMediaBlock(mediaType) {
	const blockName = {
		image: "core/image",
		slideshow: "jetpack/slideshow",
		video: "core/video",
	}[normaliseMediaType(mediaType)];

	return createBlock(blockName);
}

function createSecondTextPanelBlocks() {
	return createBlocksFromInnerBlocksTemplate(secondTextPanelTemplate[2]);
}

function getSplitSectionClassName(attributes) {
	const isTextText = isTextTextLayout(attributes);
	const layoutVariant = isTextText
		? normaliseTextTextLayoutVariant(attributes.layoutVariant)
		: normaliseLayoutVariant(attributes.layoutVariant);

	return [
		"pns-section",
		"pns-layout",
		"pns-split-section",
		"pns-site-frame-panel",
		"is-style-pns-" + layoutVariant,
		isTextText ? "is-pns-text-text" : "",
		getTextVerticalAlignmentClassName(
			"primary",
			attributes.textVerticalAlignment,
		),
		isTextText
			? getTextVerticalAlignmentClassName(
					"secondary",
					attributes.secondaryTextVerticalAlignment,
				)
			: "",
	]
		.filter(Boolean)
		.join(" ");
}

function SplitSectionEdit(props) {
	const attributes = props.attributes;
	const isTextText = isTextTextLayout(attributes);
	const hasJetpackSlideshowBlock = useSelect(function (select) {
		return Boolean(
			select("core/blocks").getBlockType("jetpack/slideshow"),
		);
	}, []);
	const mediaColumn = useSelect(
		function (select) {
			return getMediaColumn(
				select("core/block-editor").getBlock(props.clientId),
			);
		},
		[props.clientId],
	);
	const { replaceInnerBlocks, updateBlockAttributes } =
		useDispatch("core/block-editor");
	const mediaType = normaliseMediaType(
		attributes.mediaType || getMediaTypeFromColumn(mediaColumn),
	);
	const blockProps = useBlockProps({
		className: getSplitSectionClassName(attributes),
	});
	function changeMediaType(nextMediaType) {
		const normalisedMediaType = normaliseMediaType(nextMediaType);
		const isTextPanel = normalisedMediaType === textTextMediaType;

		if (
			normalisedMediaType === "slideshow" &&
			!hasJetpackSlideshowBlock
		) {
			return;
		}

		props.setAttributes({
			mediaType: normalisedMediaType,
			layoutVariant: isTextText
				? normaliseTextTextLayoutVariant(attributes.layoutVariant)
				: normaliseLayoutVariant(attributes.layoutVariant),
			secondaryTextVerticalAlignment: isTextPanel
				? attributes.secondaryTextVerticalAlignment
				: undefined,
		});

		if (!mediaColumn || normalisedMediaType === mediaType) {
			return;
		}

		updateBlockAttributes(
			mediaColumn.clientId,
			isTextPanel
				? {
						backgroundColor: "heritage-green",
						className:
							"pns-split-section__copy-column pns-split-section__text-column",
						textColor: "neutral-0",
					}
				: {
						backgroundColor: undefined,
						className:
							getMediaColumnClassName(normalisedMediaType),
						textColor: undefined,
					},
		);
		replaceInnerBlocks(
			mediaColumn.clientId,
			isTextPanel
				? createSecondTextPanelBlocks()
				: [createMediaBlock(normalisedMediaType)],
			true,
		);
	}

	return el(
		Fragment,
		null,
		el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{
						title: __("Split section", "pns-blocks"),
						initialOpen: true,
					},
					el(
						ToggleGroupControl,
						{
							label: __("Media type", "pns-blocks"),
							hideLabelFromVision: true,
							help: __(
								"Changing this replaces the second panel only. Add or edit its content afterward.",
								"pns-blocks",
							),
							value: mediaType,
							onChange: changeMediaType,
							__next40pxDefaultSize: true,
						},
						el(ToggleGroupControlOptionIcon, {
							value: textTextMediaType,
							label: __("Text", "pns-blocks"),
							icon: SPLIT_SECTION_ICON,
						}),
						el(ToggleGroupControlOptionIcon, {
							value: "image",
							label: __("Image", "pns-blocks"),
							icon: SPLIT_IMAGE_ICON,
						}),
						el(ToggleGroupControlOptionIcon, {
							value: "video",
							label: __("Video file", "pns-blocks"),
							icon: SPLIT_VIDEO_ICON,
						}),
						hasJetpackSlideshowBlock &&
							el(ToggleGroupControlOptionIcon, {
								value: "slideshow",
								label: __("Jetpack slideshow", "pns-blocks"),
								icon: SPLIT_SLIDESHOW_ICON,
							}),
					),
					el(SelectControl, {
						label: __("Layout", "pns-blocks"),
						value: normaliseLayoutVariant(
							isTextText
								? normaliseTextTextLayoutVariant(
										attributes.layoutVariant,
									)
								: attributes.layoutVariant,
						),
						options: layoutOptions,
						onChange(layoutVariant) {
							props.setAttributes({
								layoutVariant:
									normaliseLayoutVariant(layoutVariant),
							});
						},
					}),
				),
			),
		el(
			InspectorControls,
			{ group: "dimensions" },
			el(
				ToolsPanelItem,
				{
					label: isTextText
						? __("First text panel vertical alignment", "pns-blocks")
						: __("Text vertical alignment", "pns-blocks"),
					hasValue() {
						return (
							normaliseTextVerticalAlignment(
								attributes.textVerticalAlignment,
							) !== defaultTextVerticalAlignment
						);
					},
					onDeselect() {
						props.setAttributes({
							textVerticalAlignment:
								defaultTextVerticalAlignment,
						});
					},
					isShownByDefault: true,
					panelId: props.clientId,
				},
				el(SelectControl, {
					label: isTextText
						? __("First text panel vertical alignment", "pns-blocks")
						: __("Text vertical alignment", "pns-blocks"),
					value: normaliseTextVerticalAlignment(
						attributes.textVerticalAlignment,
					),
					options: textVerticalAlignmentOptions,
					onChange(textVerticalAlignment) {
						props.setAttributes({ textVerticalAlignment });
					},
					__next40pxDefaultSize: true,
				}),
			),
			isTextText &&
				el(
					ToolsPanelItem,
					{
						label: __(
							"Second text panel vertical alignment",
							"pns-blocks",
						),
						hasValue() {
							return (
								normaliseTextVerticalAlignment(
									attributes.secondaryTextVerticalAlignment,
								) !== defaultTextVerticalAlignment
							);
						},
						onDeselect() {
							props.setAttributes({
								secondaryTextVerticalAlignment:
									defaultTextVerticalAlignment,
							});
						},
						isShownByDefault: true,
						panelId: props.clientId,
					},
					el(SelectControl, {
						label: __(
							"Second text panel vertical alignment",
							"pns-blocks",
						),
						value: normaliseTextVerticalAlignment(
							attributes.secondaryTextVerticalAlignment,
						),
						options: textVerticalAlignmentOptions,
						onChange(secondaryTextVerticalAlignment) {
							props.setAttributes({
								secondaryTextVerticalAlignment,
							});
						},
						__next40pxDefaultSize: true,
					}),
				),
		),
		el(
			"div",
			blockProps,
			el(InnerBlocks, {
				template: isTextText ? textTextTemplate : imageTemplate,
				templateLock: false,
			}),
		),
	);
}

const slideshowVariation = {
	name: "slideshow",
	title: __("PNS - Split Section Slideshow", "pns-blocks"),
	icon: SPLIT_SLIDESHOW_ICON,
	description: __(
		"Two-column section with editable copy and a framed Jetpack slideshow.",
		"pns-blocks",
	),
	attributes: {
		align: "full",
		backgroundColor: "white",
		layoutVariant: "media-right",
		mediaType: "slideshow",
	},
	innerBlocks: slideshowTemplate,
	isActive: ["mediaType"],
	scope: ["block", "inserter"],
};

function registerSlideshowVariationWhenAvailable() {
	if (!hasJetpackSlideshow()) {
		return false;
	}

	const hasSlideshowVariation = getBlockVariations(
		"pns/split-section",
	).some(function (variation) {
		return variation.name === slideshowVariation.name;
	});

	if (!hasSlideshowVariation) {
		registerBlockVariation("pns/split-section", slideshowVariation);
	}

	return true;
}

registerBlockType("pns/split-section", {
	icon: SPLIT_SECTION_ICON,
	edit: SplitSectionEdit,
	save() {
		return el(InnerBlocks.Content);
	},
	variations: [
		{
			name: "text-text",
			title: __("PNS - Split Section Text | Text", "pns-blocks"),
			icon: SPLIT_SECTION_ICON,
			description: __(
				"Two editable text panels using the shared Split Section frame and content rails.",
				"pns-blocks",
			),
			attributes: {
				align: "full",
				layoutVariant: "edge-media-right",
				mediaType: textTextMediaType,
			},
			innerBlocks: textTextTemplate,
			isActive(blockAttributes) {
				return isTextTextLayout(blockAttributes);
			},
			scope: ["block", "inserter"],
		},
		{
			name: "image",
			title: __("PNS - Split Section Image", "pns-blocks"),
			icon: SPLIT_IMAGE_ICON,
			description: __(
				"Two-column section with editable copy and a replaceable framed image.",
				"pns-blocks",
			),
			attributes: {
				align: "full",
				backgroundColor: "white",
				layoutVariant: "media-right",
				mediaType: "image",
			},
			innerBlocks: imageTemplate,
			isDefault: true,
			isActive: ["mediaType"],
			scope: ["block", "inserter"],
		},
		{
			name: "video",
			title: __("PNS - Split Section Video", "pns-blocks"),
			icon: SPLIT_VIDEO_ICON,
			description: __(
				"Two-column section with editable copy and a replaceable video file.",
				"pns-blocks",
			),
			attributes: {
				align: "full",
				backgroundColor: "white",
				layoutVariant: "media-right",
				mediaType: "video",
			},
			innerBlocks: videoTemplate,
			isActive: ["mediaType"],
			scope: ["block", "inserter"],
		},
	],
});

if (!registerSlideshowVariationWhenAvailable()) {
	const unsubscribe = subscribe(function () {
		if (registerSlideshowVariationWhenAvailable()) {
			unsubscribe();
		}
	});
}
