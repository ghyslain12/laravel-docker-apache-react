import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Box, Typography, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Paper, Button, TextField, Pagination, MenuItem, Select, InputLabel, FormControl, SelectChangeEvent } from '@mui/material';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { getAll, deleteObj } from '../../../services/apiService';
import { Ticket } from '../types/ticket.types.ts';
import CustomMatButton from '../../../components/CustomMatButton.tsx';
import { AxiosResponse } from 'axios';
import { useLoadingSpinner } from '../../../hooks/useLoadingSpinner.tsx';
import { useApiError } from '../../../hooks/useApiError.tsx';

export default function TicketList() {
    const [filter, setFilter] = useState('');
    const [page, setPage] = useState(1);
    const [itemsPerPage, setItemsPerPage] = useState(5);
    const navigate = useNavigate();
    const queryClient = useQueryClient();
    const [isDeleting, setIsDeleting] = useState(false);

    const { data: objects = [], error, isLoading, isFetching } = useQuery({
        queryKey: ['tickets'],
        queryFn: async () => {
            const response: AxiosResponse<Ticket[]> = await getAll<Ticket>('/ticket');
            return response.data ?? [];
        },
        staleTime: 0,
    });

    const deleteMutation = useMutation({
        mutationFn: (id: number) => deleteObj('/ticket', id),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['tickets'] });
            setIsDeleting(false);
        },
        onError: () => {
            setIsDeleting(false);
        },
    });

    const loadingSpinner = useLoadingSpinner({ isLoading: isFetching });
    const mutationSpinner = useLoadingSpinner({ isLoading: isDeleting || deleteMutation.isPending });

    const { content: errorContent } = useApiError({ error });

    if (loadingSpinner || mutationSpinner) {
        return loadingSpinner || mutationSpinner;
    }

    if (errorContent) {
        return errorContent;
    }

    if (error) {
        return (
            <Box>
                <Typography color="error">
                    Erreur lors du chargement des tickets : {error.message}
                </Typography>
            </Box>
        );
    }

    const handleDelete = (id: number) => {
        if (window.confirm('Voulez-vous vraiment supprimer ce ticket ?')) {
            setIsDeleting(true);
            deleteMutation.mutate(id);
        }
    };

    const handleFilterChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFilter(e.target.value);
        setPage(1);
    };

    const handlePageChange = (event: React.ChangeEvent<unknown>, value: number) => {
        setPage(value);
    };

    const handleItemsPerPageChange = (event: SelectChangeEvent<number>) => {
        setItemsPerPage(event.target.value as number);
        setPage(1);
    };

    const handleView = (id: number) => {
        navigate(`/ticket/view/${id}`);
    };

    const handleEdit = (id: number) => {
        navigate(`/ticket/edit/${id}`);
    };

    const handleAdd = () => {
        navigate('/ticket/add');
    };

    const filteredTickets = Array.isArray(objects)
        ? objects.filter(
            (obj) =>
                (obj.titre ?? '').toLowerCase().includes(filter.toLowerCase()) ||
                (obj.description ?? '').toLowerCase().includes(filter.toLowerCase())
        )
        : [];

    const paginatedTickets = filteredTickets.slice(
        (page - 1) * itemsPerPage,
        page * itemsPerPage
    );

    return (
        <Box>
            <Typography variant="h5" gutterBottom sx={{ fontWeight: 'bold' }}>
                Liste des tickets
            </Typography>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 2 }}>
                <TextField
                    label="Filtrer"
                    value={filter}
                    onChange={handleFilterChange}
                    variant="filled"
                    size="small"
                    sx={{ width: 200 }}
                />
                <CustomMatButton variant="contained" onClick={handleAdd}>
                    Ajouter ticket
                </CustomMatButton>
            </Box>
            {filteredTickets.length === 0 ? (
                <Typography>Aucun ticket trouvé.</Typography>
            ) : (
                <Box>
                    <TableContainer component={Paper} elevation={8}>
                        <Table>
                            <TableHead>
                                <TableRow>
                                    <TableCell>ID</TableCell>
                                    <TableCell>Titre</TableCell>
                                    <TableCell>Description</TableCell>
                                    <TableCell>Sale</TableCell>
                                    <TableCell>Actions</TableCell>
                                </TableRow>
                            </TableHead>
                            <TableBody>
                                {paginatedTickets.map((obj) => (
                                    <TableRow key={obj.id}>
                                        <TableCell>{obj.id}</TableCell>
                                        <TableCell>{obj.titre ?? 'N/A'}</TableCell>
                                        <TableCell>{obj.description ?? 'N/A'}</TableCell>
                                        <TableCell>
                                            {obj.sales?.length > 0 ? obj.sales[0].titre ?? 'N/A' : 'Aucune vente associée'}
                                        </TableCell>
                                        <TableCell>
                                            <Button
                                                onClick={() => handleView(obj.id)}
                                                color="primary"
                                                size="medium"
                                                sx={{ mr: 1, color: 'black', fontWeight: '430', textTransform: 'none' }}
                                            >
                                                Voir
                                            </Button>
                                            <Button
                                                onClick={() => handleEdit(obj.id)}
                                                color="primary"
                                                size="medium"
                                                sx={{ mr: 1, color: 'black', fontWeight: '430', textTransform: 'none' }}
                                            >
                                                Modifier
                                            </Button>
                                            <Button
                                                onClick={() => handleDelete(obj.id)}
                                                color="error"
                                                size="medium"
                                                disabled={deleteMutation.isPending}
                                                sx={{ mr: 1, color: 'red', fontWeight: '430', textTransform: 'none' }}
                                            >
                                                Supprimer
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </TableContainer>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mt: 2 }}>
                        <FormControl variant="outlined" size="small">
                            <InputLabel>Items par page</InputLabel>
                            <Select
                                value={itemsPerPage}
                                onChange={handleItemsPerPageChange}
                                label="Items par page"
                            >
                                <MenuItem value={5}>5</MenuItem>
                                <MenuItem value={10}>10</MenuItem>
                                <MenuItem value={20}>20</MenuItem>
                            </Select>
                        </FormControl>
                        <Pagination
                            count={Math.ceil(filteredTickets.length / itemsPerPage)}
                            page={page}
                            onChange={handlePageChange}
                            size="small"
                        />
                        <Typography variant="body2">
                            1 - {paginatedTickets.length} sur {filteredTickets.length}
                        </Typography>
                    </Box>
                </Box>
            )}
        </Box>
    );
}