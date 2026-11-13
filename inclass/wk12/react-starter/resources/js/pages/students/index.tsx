import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import { index } from '@/routes/students';

type Student = { id: number; name: string; major: string; year: number };

export default function Students({ students }: { students: Student[] }) {
    return (
        <>
            <Head title="Students" />
            <div className="flex flex-col gap-4 p-4">
                <div className="grid gap-4 md:grid-cols-3">
                    {students.map((student) => (
                        <Card key={student.id}>
                            <CardHeader>
                                <CardTitle>{student.name}</CardTitle>
                            </CardHeader>
                            <CardContent className="flex items-center gap-2">
                                <Badge>{student.major}</Badge>
                                <span className="text-sm text-muted-foreground">
                                    Class of {student.year}
                                </span>
                            </CardContent>
                        </Card>
                    ))}
                </div>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Major</TableHead>
                            <TableHead>Class of</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {students.map((student) => (
                            <TableRow key={student.id}>
                                <TableCell>{student.name}</TableCell>
                                <TableCell>{student.major}</TableCell>
                                <TableCell>{student.year}</TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
                <Button asChild className="w-fit">
                    <Link href={dashboard()}>Back to the dashboard</Link>
                </Button>
            </div>
        </>
    );
}

Students.layout = {
    breadcrumbs: [
        {
            title: 'Students',
            href: index(),
        },
    ],
};
