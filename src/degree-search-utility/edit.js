/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit() {
	// Static JSON data
	const programData = [
		{
			id: 123,
			major: 'Accounting',
			degrees: [
				{ name: 'MACC', url: 'https://haslam.utk.edu/accounting/macc/' }
			],
			concentrations: [
				{ name: 'Audit and Controls', online: true },
				{ name: 'Information Management', online: false },
				{ name: 'Taxation', online: false },
			],
		},
		{
			id: 124,
			major: 'Accounting',
			degrees: [
				{ name: 'BSBA', url: 'https://haslam.utk.edu/accounting/bachelors/' }
			],
			concentrations: [
				{ name: 'Collateral Option', online: false },
				{ name: 'Integrated Business and Engineering', online: false },
				{ name: 'International Business', online: false },
			],
		},
	];

	return (
		<section { ...useBlockProps() } className="programs-filters alignwide" id="filters">
			{ __(
			<div className="programs-filters-results" id="program-results">
				<div className="programs-filters-headings">
					<h2 className="programs-filters-heading">Program</h2>
					<h2 className="programs-filters-heading">Degree / Certificate</h2>
					<h2 className="programs-filters-heading">Concentration</h2>
				</div>
				{programData.map((program) => (
					<div key={program.id} className="program-entry">
						<div className="program-name">
								<p>{program.major}</p>
						</div>
						<ul className="degree-list">
							{program.degrees.map((degree, index) => (
								<li key={index}>
									<a href={degree.url} target="_blank" rel="noopener noreferrer">
										{degree.name}
									</a>
								</li>
							))}
						</ul>
						<ul className="concentration-list">
							{program.concentrations.map((concentration, index) => (
								<li key={index}>
									{concentration.name}{' '}
									{concentration.online && (
										<span className="online-tag">Online</span>
									)}
								</li>
							))}
						</ul>
					</div>
				))}
			</div>
			) }
		</section>
	);
}
