/**
 * BLOCK: cra/webcast-speaker
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

import './editor.scss';
import './style.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Button } = wp.components;
import { RichText, MediaUpload } from '@wordpress/block-editor';

const attributes = {
	speakerImage: {
		type: 'string',
		source: 'attribute',
		selector: 'img',
		attribute: 'src',
	},
	speakerImageId: {
		type: 'number',
	},
	speakerName: {
		type: 'array',
		source: 'children',
		selector: '.speaker-name',
	},
	speakerJob: {
		type: 'array',
		source: 'children',
		selector: '.speaker-job',
	},
	speakerCompany: {
		type: 'array',
		source: 'children',
		selector: '.speaker-company',
	},
	speakerDescription: {
		type: 'array',
		source: 'children',
		selector: '.speaker-description',
	},
};

/**
 * The edit function describes the structure of your block in the context of the editor.
 * This represents what the editor will render when the block is used.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
 *
 * @param {Object} props Props.
 * @returns {JSX.Element} JSX Component.
 */
const edit = ( {
	attributes: { speakerImage, speakerImageId, speakerName, speakerJob, speakerCompany, speakerDescription },
	setAttributes, className,
} ) => {
	return (
		<div className={ className }>
			<div className="speaker-wrap -grid">
				<div className="speaker-image">
					<MediaUpload
						onSelect={ media => setAttributes( { speakerImage: media.url, speakerImageId: media.id } ) }
						type="image"
						value={ speakerImageId }
						render={ ( { open } ) => (
							<Button
								className={ speakerImageId ? 'image-button' : 'button button-large' }
								onClick={ open }
							>
								{ ! speakerImageId ? __( 'Upload Image' ) : <img src={ speakerImage } alt="Speaker" /> }
							</Button>
						) }
					/>
				</div>
				<div className="speaker-info">
					<div className="speaker-about">
						<RichText
							tagName="p"
							className="speaker-name"
							placeholder={ __( 'Name' ) }
							value={ speakerName }
							onChange={ value => setAttributes( { speakerName: value } ) }
						/>
						<RichText
							tagName="p"
							className="speaker-job"
							placeholder={ __( 'Job' ) }
							value={ speakerJob }
							onChange={ value => setAttributes( { speakerJob: value } ) }
						/>
						<RichText
							tagName="p"
							className="speaker-company"
							placeholder={ __( 'Company' ) }
							value={ speakerCompany }
							onChange={ value => setAttributes( { speakerCompany: value } ) }
						/>
					</div>
					<RichText
						tagName="div"
						multiline="p"
						className="speaker-description"
						placeholder={ __( 'Write the description (optional)' ) }
						value={ speakerDescription }
						onChange={ value => setAttributes( { speakerDescription: value } ) }
					/>
				</div>
			</div>
		</div>
	);
};

/**
 * The save function defines the way in which the different attributes should be combined
 * into the final markup, which is then serialized by Gutenberg into post_content.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
 *
 * @param {Object} props Props.
 * @returns {JSX.Element} JSX Frontend HTML.
 */
const save = ( {
	attributes: { speakerImage, speakerName, speakerJob, speakerCompany, speakerDescription },
	className,
} ) => {
	return (
		<div className={ className }>
			<div className={ 'speaker-wrap overflow' + ( speakerImage ? ' -grid' : '' ) }>
				<SpeakerImage src={ speakerImage } alt="Speaker" />
				<div className={ 'speaker-info' }>
					<div className={ 'speaker-about' }>
						<RichText.Content
							tagName="p"
							className="speaker-name"
							value={ speakerName }
						/>
						<RichText.Content
							tagName="p"
							className="speaker-job"
							value={ speakerJob }
						/>
						<RichText.Content
							tagName="p"
							className="speaker-company"
							value={ speakerCompany }
						/>
					</div>
					<RichText.Content
						tagName="div"
						className="speaker-description"
						value={ speakerDescription }
					/>
				</div>
			</div>
		</div>
	);
};

/**
 * Speaker Image
 * @param {Object} props Props.
 * @returns {JSX.Element|string} JSX Frontend HTML.
 */
const SpeakerImage = ( { src, alt } ) => {
	return src ? (
		<figure className="speaker-image">
			<img src={ src } alt={ alt || '' } />
		</figure>
	) : '';
};

/**
 * Register: aa Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType( 'cra/webcast-speaker', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'CRA - Webcast Speaker' ), // Block title.
	icon: 'groups', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'CRA - Speaker' ),
		__( 'CRA - Webcast Speaker' ),
		__( 'cra-block-webcast-speaker' ),
		__( 'CRA Block: Webcast Speaker' ),
	],
	attributes: attributes,
	edit: props => props.isSelected ? edit( props ) : save( props ),
	save: save,
} );
