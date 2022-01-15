import { useEffect, useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { Stack, Typography } from '@mui/material';
import { __ } from '@wordpress/i18n';

import { STORE_NAME } from './store';
import ActionButton from './components/ActionButton';
import Messages from './components/Messages';
import NFTs from './components/NFTs';
import { hasMetaMask } from './util';

export default function App() {
	const { initStore } = useDispatch( STORE_NAME );
	const { isUserLoggedIn } = useSelect(
		( select ) => ( {
			isUserLoggedIn: select( STORE_NAME ).isUserLoggedIn(),
		} ),
		[]
	);
	const [ status, setStatus ] = useState( {} );

	useEffect( () => {
		const initData = window.wnftdData;
		initStore( initData );
	}, [] );

	if ( ! hasMetaMask() ) {
		return (
			<Stack>
				<Alert severity="info">
					{ __(
						'This product can only be downloaded by the owners of specific NFTs. MetaMask was not detected in the current browser to confirm NFT ownership.',
						'wnftd'
					) }
				</Alert>
			</Stack>
		);
	}

	return (
		<Stack sx={ { marginBottom: '15px' } } spacing={ 1 }>
			<Typography variant="h4">
				{ __( 'NFTs required for download', 'wnftd' ) }
			</Typography>
			<Messages />
			<NFTs />
			<ActionButton status={ status } setStatus={ setStatus } />
		</Stack>
	);
}
