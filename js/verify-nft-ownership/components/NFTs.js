import { useEffect, useState } from '@wordpress/element';
import {
	Stack,
	Card,
	CardContent,
	CardMedia,
	Typography,
	Link,
} from '@mui/material';
import { __ } from '@wordpress/i18n';
import placeholder from './placeholder.png';

export default function NFTs() {
	const [ items, setItems ] = useState( [] );

	useEffect( () => {
		if ( ! window?.wnftdData?.requiredNfts ) {
			return;
		}

		setItems( window.wnftdData.requiredNfts );
	}, [] );

	return (
		<Stack spacing={ 1 }>
			{ items.map( ( item, index ) => (
				<Card sx={ { display: 'flex' } } key={ index }>
					<CardMedia
						component="img"
						image={ item.image ? item.image : placeholder }
						alt="NFT image"
						sx={ { width: 'auto', maxHeight: 120 } }
					/>
					<CardContent>
						<Typography variant="h5">{ item.name }</Typography>
						{ item.buyUrl && (
							<Link variant="subtitle1" href={ item.buyUrl }>
								{ __( 'Buy', 'wnftd' ) }
							</Link>
						) }
					</CardContent>
				</Card>
			) ) }
		</Stack>
	);
}
